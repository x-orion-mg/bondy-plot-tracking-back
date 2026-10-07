<?php

namespace App\Service;

use App\Utils\PathName;
use App\Utils\Utils;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Asset\Packages;

class ExportExcel
{
    private string $projectDir;
	private Packages $assetsManager;
    public function __construct(
        KernelInterface $kernel,
        Packages $assetsManager,
    )
    {
        $this->projectDir = $kernel->getProjectDir();
	    $this->assetsManager = $assetsManager;
    }

    /**
     * @param array $data
     * @param string $partnerName
     * @return array
     */
    public function exportToExcel(array $data, string $partnerName): array
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Définir les valeurs d'en-tête
        $title = "Export des clients finaux";
        $exportDate = (new \DateTime())->format('d/m/Y H:i');

        // Ajouter l'en-tête
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', "PARTENAIRE : " . $partnerName);
        $sheet->setCellValue('A3', "DATE D'EXPORT : " . $exportDate);
        $sheet->mergeCells('A2:E2');
        $sheet->mergeCells('A3:E3');
        $sheet->getStyle('A2:A3')->getFont()->setBold(true);

        // Ajouter les en-têtes de colonnes (ligne 5)
        $headers = ['ID', 'DATE D\'IMPORT', 'NOM', 'EMAIL', 'CODE QR'];
        $colIndex = 'A';
        foreach ($headers as $header) {
            $sheet->setCellValue($colIndex . '5', $header);
            $colIndex++;
        }

        // Appliquer un style aux en-têtes
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F81BD']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A5:E5')->applyFromArray($headerStyle);

        // Remplir les données à partir de la ligne 6
        $row = 6;
        foreach ($data as $item) {
            $sheet->setCellValue('A' . $row, $item['userId'])
                ->setCellValue('B' . $row,  $item['createdAt'])
                ->setCellValue('C' . $row, $item['lastName'].' '. $item['firstName'])
                ->setCellValue('D' . $row, $item['email'] ?? '')
                ->setCellValue('E' . $row, $item['qrCode'] ?? '-');
            $row++;
        }

        // Définir une plage de cellules pour la mise en forme en tableau
        $tableRange = 'A5:E' . ($row - 1);
        $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Convertir la plage en tableau Excel
        $table = new \PhpOffice\PhpSpreadsheet\Worksheet\Table($tableRange);
        $table->setName('Customers');
        $sheet->addTable($table);

        // Ajuster automatiquement la largeur des colonnes
        foreach (range('A', 'E') as $columnID) {
            $sheet->getColumnDimension($columnID)->setAutoSize(true);
        }

        $directory = PathName::EXPORT_EXCEL_CUSTOMERS_FINALS;
        $filename = 'export_customer_'. strtolower($partnerName) .'_'. date('Ymd_His') . '.xlsx';
        $filePath = $directory . $filename;

        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return [
            'url'      => $this->assetsManager->getUrl($filePath),
            'fileName' => $filename
        ];
    }
}
