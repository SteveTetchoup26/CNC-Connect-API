<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CandidatsConcoursExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $candidatures;
    protected $concours;

    public function __construct($candidatures, $concours)
    {
        $this->candidatures = $candidatures;
        $this->concours = $concours;
    }

    public function collection()
    {
        return $this->candidatures->map(function ($candidature, $index) {
            return [
                'N°' => $index + 1,
                'Nom' => $candidature->candidat?->utilisateur?->nom ?? 'N/A',
                'Prénom' => $candidature->candidat?->utilisateur?->prenom ?? 'N/A',
                'Email' => $candidature->candidat?->utilisateur?->email ?? 'N/A',
                'Sexe' => $candidature->candidat?->sexe === 'M' ? 'Masculin' : ($candidature->candidat?->sexe === 'F' ? 'Féminin' : 'N/A'),
                'N° CNI' => $candidature->candidat?->numero_cni ?? 'N/A',
                'Statut' => $candidature->statut ?? 'N/A',
                'Code Candidat' => $candidature->code_candidat_def ?? $candidature->code_candidat_prov ?? 'N/A',
                'Centre d\'examen' => $candidature->centreExamen?->libelle_centre ?? 'Non affecté',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'N°',
            'Nom',
            'Prénom',
            'Email',
            'Sexe',
            'N° CNI',
            'Statut',
            'Code Candidat',
            'Centre d\'examen',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style pour l'en-tête
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    public function title(): string
    {
        return 'Candidats ' . \Illuminate\Support\Str::limit($this->concours->libelle_concours, 25);
    }
}
