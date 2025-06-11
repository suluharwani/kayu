<?php namespace App\Controllers;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\StockModel;

class Export extends BaseController
{
    public function exportStock()
    {
        $stockModel = new StockModel();
        $stock = $stockModel->getStockGlobal();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header
        $sheet->setCellValue('A1', 'No');
        $sheet->setCellValue('B1', 'Gudang');
        $sheet->setCellValue('C1', 'Kode Kayu');
        $sheet->setCellValue('D1', 'Jenis Kayu');
        $sheet->setCellValue('E1', 'Dimensi (cm)');
        $sheet->setCellValue('F1', 'Volume (m3)');
        $sheet->setCellValue('G1', 'Grade');
        $sheet->setCellValue('H1', 'Kualitas');
        $sheet->setCellValue('I1', 'Quantity');
        $sheet->setCellValue('J1', 'Total Volume');
        
        // Data
        $no = 1;
        $row = 2;
        foreach($stock as $s) {
            $sheet->setCellValue('A'.$row, $no++);
            $sheet->setCellValue('B'.$row, $s['nama_gudang']);
            $sheet->setCellValue('C'.$row, $s['kode_kayu']);
            $sheet->setCellValue('D'.$row, $s['nama_jenis']);
            $sheet->setCellValue('E'.$row, $s['panjang'].'x'.$s['lebar'].'x'.$s['tebal']);
            $sheet->setCellValue('F'.$row, $s['volume']);
            $sheet->setCellValue('G'.$row, $s['grade']);
            $sheet->setCellValue('H'.$row, $s['kualitas']);
            $sheet->setCellValue('I'.$row, $s['quantity']);
            $sheet->setCellValue('J'.$row, '=F'.$row.'*I'.$row);
            $row++;
        }
        
        // Format
        foreach(range('A','J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle('A1:J1')->getFont()->setBold(true);
        
        $writer = new Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="stock_kayu.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }
}