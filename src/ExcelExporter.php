<?php
namespace App;

class ExcelExporter {
    
    public function exportBets($bets) {
        // Verifica se PhpSpreadsheet está disponível
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            return $this->exportWithSpreadsheet($bets);
        } else {
            // Fallback: exporta como CSV
            return $this->exportCSV($bets);
        }
    }
    
    private function exportWithSpreadsheet($bets) {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $sheet->setTitle('Apostas');
        
        $headers = ['Data', 'Valor Apostado', 'ODD', 'Green', 'Red', 'Usuário'];
        $sheet->fromArray($headers, null, 'A1');
        
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        $row = 2;
        foreach ($bets as $bet) {
            $sheet->setCellValue('A' . $row, $bet['data']);
            $sheet->setCellValue('B' . $row, 'R$ ' . number_format($bet['valor_apostado'], 2, ',', '.'));
            $sheet->setCellValue('C' . $row, number_format($bet['odd'], 2, ',', '.'));
            $sheet->setCellValue('D' . $row, $bet['green'] ? 'R$ ' . number_format($bet['green'], 2, ',', '.') : '');
            $sheet->setCellValue('E' . $row, $bet['red'] !== null ? 'R$ ' . number_format($bet['red'], 2, ',', '.') : '');
            $sheet->setCellValue('F' . $row, $bet['usuario']);
            
            if ($bet['green']) {
                $sheet->getStyle('D' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
            }
            if ($bet['red'] !== null) {
                $sheet->getStyle('E' . $row)->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
            }
            
            $row++;
        }
        
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $filename = 'apostas_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($filepath);
        
        return [
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }
    
    public function exportCSV($bets) {
        $filename = 'apostas_' . date('Y-m-d_H-i-s') . '.csv';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        $fp = fopen($filepath, 'w');
        
        // BOM para Excel reconhecer UTF-8
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($fp, ['Data', 'Valor Apostado', 'ODD', 'Green', 'Red', 'Usuario'], ';');
        
        // Dados
        foreach ($bets as $bet) {
            fputcsv($fp, [
                $bet['data'],
                $bet['valor_apostado'],
                $bet['odd'],
                $bet['green'] ?? '',
                $bet['red'] ?? '',
                $bet['usuario']
            ], ';');
        }
        
        fclose($fp);
        
        return [
            'filepath' => $filepath,
            'filename' => $filename
        ];
    }
}