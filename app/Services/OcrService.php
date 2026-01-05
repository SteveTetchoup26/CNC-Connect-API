<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OcrService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('ocr');
    }

    /**
     * Extraire les informations de paiement d'une image ou PDF
     *
     * @param string $fichierPath Chemin du fichier
     * @return array|null
     */
    public function extraireInfosPaiement($fichierPath)
    {
        try {
            $extension = strtolower(pathinfo($fichierPath, PATHINFO_EXTENSION));

            // Si c'est un PDF, le convertir en image d'abord
            if ($extension === 'pdf') {
                $fichierPath = $this->convertirPdfEnImage($fichierPath);
                if (!$fichierPath) {
                    Log::error('Impossible de convertir le PDF en image');
                    return null;
                }
            }

            // Exécuter l'OCR
            $ocr = new TesseractOCR($fichierPath);

            // Configuration du chemin Tesseract pour Windows
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                $tesseractPath = config('ocr.tesseract_path_windows');

                // Si pas configuré dans .env, utiliser le chemin par défaut
                if (!$tesseractPath) {
                    $tesseractPath = 'C:/Program Files/Tesseract-OCR/tesseract.exe';
                }

                // Convertir les backslashes en slashes pour Windows
                $tesseractPath = str_replace('\\', '/', $tesseractPath);

                Log::info("Chemin Tesseract utilisé: " . $tesseractPath);

                if (file_exists($tesseractPath)) {
                    $ocr->executable($tesseractPath);
                    Log::info("Tesseract exécutable configuré avec succès");
                } else {
                    Log::error("Tesseract non trouvé au chemin: " . $tesseractPath);
                }
            }

            $ocr->lang(...$this->config['langues']);

            // Options pour améliorer la reconnaissance
            $ocr->psm(6); // Assume a single uniform block of text
            $ocr->oem(1); // LSTM neural net mode

            $texte = $ocr->run();

            Log::info('Texte extrait par OCR:', ['texte' => $texte]);

            // Nettoyer le fichier temporaire si c'était un PDF
            if ($extension === 'pdf' && file_exists($fichierPath)) {
                @unlink($fichierPath);
            }

            // Extraire les informations
            $infos = [
                'montant' => $this->extraireMontant($texte),
                'banque' => $this->extraireNomBanque($texte),
                'numero_compte' => $this->extraireNumeroCompte($texte),
                'texte_complet' => $texte
            ];

            return $infos;

        } catch (\Exception $e) {
            Log::error('Erreur OCR: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Convertir un PDF en image pour l'OCR
     * Nécessite Imagick ou GhostScript
     */
    private function convertirPdfEnImage($pdfPath)
    {
        try {
            // Vérifier si Imagick est disponible
            if (extension_loaded('imagick')) {
                $imagick = new Imagick();
                $imagick->setResolution(300, 300);
                $imagick->readImage($pdfPath . '[0]'); // Lire la première page
                $imagick->setImageFormat('jpg');

                $tempPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.jpg';
                $imagick->writeImage($tempPath);
                $imagick->clear();

                return $tempPath;
            }

            // Alternative avec GhostScript
            $tempPath = sys_get_temp_dir() . '/ocr_' . uniqid() . '.jpg';
            $command = sprintf(
                'gs -dNOPAUSE -sDEVICE=jpeg -r300 -dFirstPage=1 -dLastPage=1 -sOutputFile=%s %s -c quit',
                escapeshellarg($tempPath),
                escapeshellarg($pdfPath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar === 0 && file_exists($tempPath)) {
                return $tempPath;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Erreur conversion PDF: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extraire le montant du texte
     */
    private function extraireMontant($texte)
    {
        $patterns = $this->config['patterns']['montant'] ?? [
            '/montant[:\s]+([0-9\s]+[,.]?[0-9]*)\s*(FCFA|CFA|F)/i',
            '/([0-9\s]+[,.]?[0-9]*)\s*(FCFA|CFA|F)/i',
            '/total[:\s]+([0-9\s]+[,.]?[0-9]*)/i',
            '/somme[:\s]+([0-9\s]+[,.]?[0-9]*)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $texte, $matches)) {
                $montant = str_replace([' ', ','], ['', '.'], $matches[1]);
                return floatval($montant);
            }
        }

        return null;
    }

    /**
     * Extraire le nom de la banque
     */
    private function extraireNomBanque($texte)
    {
        $banques = $this->config['banques'] ?? [];
        $texteUpper = strtoupper($texte);

        foreach ($banques as $banque) {
            if (strpos($texteUpper, $banque) !== false) {
                return $banque;
            }
        }

        // Pattern générique pour détecter une banque
        if (preg_match('/(?:BANQUE|BANK)\s+([A-Z\s]+)/i', $texte, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }

    /**
     * Extraire le numéro de compte
     */
    private function extraireNumeroCompte($texte)
    {
        $patterns = $this->config['patterns']['compte'] ?? [
            '/compte[:\s]+([0-9\-\s]+)/i',
            '/n[°o\s]*compte[:\s]*([0-9\-\s]+)/i',
            '/RIB[:\s]+([0-9\-\s]+)/i',
            '/IBAN[:\s]+([A-Z0-9\-\s]+)/i',
            '/([0-9]{10,})/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $texte, $matches)) {
                $numero = preg_replace('/\s+/', '', $matches[1]);
                if (strlen($numero) >= 10) {
                    return $numero;
                }
            }
        }

        return null;
    }

    /**
     * Vérifier si les informations extraites correspondent aux frais du concours
     */
    public function verifierCorrespondance($infosExtraites, $fraisConcours, $tolerance = null)
    {
        $tolerance = $tolerance ?? $this->config['tolerance_montant'] ?? 5;
        $erreurs = [];
        $correspondances = [];

        // Vérifier le montant
        if ($infosExtraites['montant']) {
            $montantExtraite = $infosExtraites['montant'];
            $montantAttendu = $fraisConcours->montant_frais;

            $difference = abs($montantExtraite - $montantAttendu);
            $pourcentageDifference = ($difference / $montantAttendu) * 100;

            if ($pourcentageDifference <= $tolerance) {
                $correspondances['montant'] = true;
            } else {
                $erreurs[] = "Le montant extrait ({$montantExtraite} FCFA) ne correspond pas au montant attendu ({$montantAttendu} FCFA). Différence: " . round($pourcentageDifference, 2) . "%";
                $correspondances['montant'] = false;
            }
        } else {
            $erreurs[] = "Impossible d'extraire le montant de la fiche de paiement";
            $correspondances['montant'] = false;
        }

        // Vérifier la banque
        if ($infosExtraites['banque']) {
            $banqueExtraite = strtoupper(trim($infosExtraites['banque']));
            $banqueAttendue = strtoupper(trim($fraisConcours->nom_banque));

            if (strpos($banqueExtraite, $banqueAttendue) !== false ||
                strpos($banqueAttendue, $banqueExtraite) !== false ||
                similar_text($banqueExtraite, $banqueAttendue) / max(strlen($banqueExtraite), strlen($banqueAttendue)) > 0.7) {
                $correspondances['banque'] = true;
            } else {
                $erreurs[] = "La banque extraite ({$infosExtraites['banque']}) ne correspond pas à la banque attendue ({$fraisConcours->nom_banque})";
                $correspondances['banque'] = false;
            }
        } else {
            $erreurs[] = "Impossible d'extraire le nom de la banque de la fiche de paiement";
            $correspondances['banque'] = false;
        }

        // Vérifier le numéro de compte
        if ($infosExtraites['numero_compte']) {
            $numeroExtraite = preg_replace('/[\s\-]/', '', $infosExtraites['numero_compte']);
            $numeroAttendu = preg_replace('/[\s\-]/', '', $fraisConcours->numero_compte);

            if ($numeroExtraite === $numeroAttendu) {
                $correspondances['numero_compte'] = true;
            } else {
                $erreurs[] = "Le numéro de compte extrait ne correspond pas au numéro de compte attendu";
                $correspondances['numero_compte'] = false;
            }
        } else {
            $erreurs[] = "Impossible d'extraire le numéro de compte de la fiche de paiement";
            $correspondances['numero_compte'] = false;
        }

        return [
            'valide' => empty($erreurs),
            'correspondances' => $correspondances,
            'erreurs' => $erreurs,
            'infos_extraites' => $infosExtraites
        ];
    }

    /**
     * Sauvegarder les résultats de vérification OCR
     */
    public function sauvegarderVerification($fichePaiementId, $verification)
    {
        $fichePaiement = \App\Models\FichePaiement::find($fichePaiementId);

        if ($fichePaiement) {
            $fichePaiement->update([
                'ocr_verifie' => true,
                'ocr_donnees' => json_encode($verification['infos_extraites']),
                'ocr_date_verification' => now(),
                'ocr_erreurs' => !empty($verification['erreurs']) ? implode(' | ', $verification['erreurs']) : null
            ]);
        }
    }
}
