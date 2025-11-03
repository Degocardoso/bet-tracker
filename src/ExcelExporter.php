<?php
namespace App;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ExcelExporter {
    
    public function exportBets($bets) {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Define o título
        $sheet->setTitle('Apostas');
        
        // Cabeçalhos
        $headers = ['Data', 'Valor Apostado', 'ODD', 'Green', 'Red', 'Usuário'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Estilo do cabeçalho
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER
            ]
        ];
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Dados
        $row = 2;
        foreach ($bets as $bet) {
            $sheet->setCellValue('A' . $row, $bet['data']);
            $sheet->setCellValue('B' . $row, 'R$ ' . number_format($bet['valor_apostado'], 2, ',', '.'));
            $sheet->setCellValue('C' . $row, number_format($bet['odd'], 2, ',', '.'));
            $sheet->setCellValue('D' . $row, $bet['green'] ? 'R$ ' . number_format($bet['green'], 2, ',', '.') : '');
            $sheet->setCellValue('E' . $row, $bet['red'] !== null ? 'R$ ' . number_format($bet['red'], 2, ',', '.') : '');
            $sheet->setCellValue('F' . $row, $bet['usuario']);
            
            // Destaca greens em verde e reds em vermelho
            if ($bet['green']) {
                $sheet->getStyle('D' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('C6EFCE');
            }
            if ($bet['red'] !== null) {
                $sheet->getStyle('E' . $row)->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('FFC7CE');
            }
            
            $row++;
        }
        
        // Ajusta largura das colunas
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Gera o arquivo
        $filename = 'apostas_' . date('Y-m-d_H-i-s') . '.xlsx';
        $filepath = sys_get_temp_dir() . '/' . $filename;
        
        $writer = new Xlsx($spreadsheet);
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
