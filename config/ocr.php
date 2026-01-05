<?php

return [
    /*
    |--------------------------------------------------------------------------
    | OCR Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration pour la vérification OCR des fiches de paiement
    |
    */

    // Tolérance pour la vérification du montant (en pourcentage)
    'tolerance_montant' => env('OCR_TOLERANCE_MONTANT', 5),

    // Activer/désactiver la vérification OCR
    'actif' => env('OCR_ACTIF', true),

    // Rejeter l'inscription si l'OCR échoue
    'rejeter_si_echec' => env('OCR_REJETER_SI_ECHEC', true),

    // Langues à utiliser pour Tesseract
    'langues' => ['fra', 'eng'],

    // Liste des banques à reconnaître (à adapter selon votre pays)
    'banques' => [
        'ECOBANK',
        'SGBCI',
        'BICICI',
        'NSIA',
        'BOA',
        'UBA',
        'CORIS BANK',
        'ORABANK',
        'BRIDGE BANK',
        'BANQUE ATLANTIQUE',
        'SOCIETE GENERALE',
        'BNI',
        'BGFI BANK',
        'BANQUE POPULAIRE',
        'CREDIT DU NORD',
    ],

    // Patterns de regex pour extraire les informations
    'patterns' => [
        'montant' => [
            '/montant[:\s]+([0-9\s]+[,.]?[0-9]*)\s*(FCFA|CFA|F)/i',
            '/([0-9\s]+[,.]?[0-9]*)\s*(FCFA|CFA|F)/i',
            '/total[:\s]+([0-9\s]+[,.]?[0-9]*)/i',
            '/somme[:\s]+([0-9\s]+[,.]?[0-9]*)/i',
        ],
        'compte' => [
            '/compte[:\s]+([0-9\-\s]+)/i',
            '/n[°o\s]*compte[:\s]*([0-9\-\s]+)/i',
            '/RIB[:\s]+([0-9\-\s]+)/i',
            '/IBAN[:\s]+([A-Z0-9\-\s]+)/i',
        ],
    ],
];
