<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;

class ConcoursExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithTitle
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Concours',
            'Session',
            'Date limite',
            'Date examen',
            'Frais (FCFA)',
            'Banque',
            'Numéro de compte',
            'Nombre de filières',
        ];
    }

    public function title(): string
    {
        return 'Concours ' . Carbon::now()->format('m-Y');
    }

    public function styles(Worksheet $sheet)
    {
        // Style de l'en-tête
        $sheet->getStyle('A1:H1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        // Bordures pour toutes les cellules
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        $sheet->getStyle("A1:{$lastColumn}{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['rgb' => 'CCCCCC'],
                ],
            ],
        ]);

        // Alignement des colonnes
        $sheet->getStyle('A:A')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('B:B')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('C:D')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('E:E')->getAlignment()->setHorizontal('right');
        $sheet->getStyle('F:G')->getAlignment()->setHorizontal('left');
        $sheet->getStyle('H:H')->getAlignment()->setHorizontal('center');

        // Hauteur de ligne pour l'en-tête
        $sheet->getRowDimension(1)->setRowHeight(25);

        return [];
    }
}
