<?php
namespace App;

class OCRProcessor {
    
    public function processImage($imagePath) {
        try {
            if (class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
                return $this->processWithTesseract($imagePath);
            } else {
                return $this->getEmptyData();
            }
        } catch (\Exception $e) {
            error_log("Erro no OCR: " . $e->getMessage());
            return $this->getEmptyData();
        }
    }
    
    private function processWithTesseract($imagePath) {
        $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($imagePath);
        $text = $ocr->lang('por')->run();
        return $this->extractBetData($text);
    }
    
    private function getEmptyData() {
        return [
            'data' => date('Y-m-d'),
            'valor_apostado' => '',
            'odd' => '',
            'retorno' => '',
            'green' => null,
            'red' => null
        ];
    }
    
    private function extractBetData($text) {
        $data = [
            'data' => date('Y-m-d'),
            'valor_apostado' => '',
            'odd' => '',
            'retorno' => '',
            'green' => null,
            'red' => null
        ];
        
        $text = $this->normalizeText($text);
        
        // LOG para debug
        error_log("OCR Text: " . $text);
        
        // Extrai DATA no formato dd/mm/yyyy
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $text, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            $data['data'] = "$year-$month-$day";
        }
        
        // Extrai VALOR APOSTADO
        // Padrões: "Aposta R$130,00" ou "Aposta 130.00" ou "R$130.00 Simples"
        if (preg_match('/aposta\s+r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
            error_log("Valor Apostado encontrado: " . $matches[1]);
        }
        
        // Extrai ODD
        // Busca número com vírgula ou ponto (1,68 ou 1.68)
        // Pode estar sozinho ou após texto
        $numbers = [];
        preg_match_all('/\b(\d{1,2}[,\.]\d{2})\b/', $text, $numbers);
        
        if (!empty($numbers[1])) {
            foreach ($numbers[1] as $num) {
                $parsed = $this->parseNumber($num);
                // ODD geralmente está entre 1.00 e 50.00
                if ($parsed >= 1.0 && $parsed <= 50.0 && empty($data['odd'])) {
                    $data['odd'] = $parsed;
                    error_log("ODD encontrada: " . $num);
                    break;
                }
            }
        }
        
        // Extrai RETORNO
        // Padrões: "Retorno R$219,66" ou "Retorno 219.66"
        if (preg_match('/retorno\s+r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $data['retorno'] = $this->parseNumber($matches[1]);
            error_log("Retorno encontrado: " . $matches[1]);
        }
        
        // CALCULA GREEN OU RED automaticamente
        if ($data['retorno'] && $data['valor_apostado']) {
            if ($data['retorno'] > $data['valor_apostado']) {
                // GANHOU (Green)
                $data['green'] = $data['retorno'];
                $data['red'] = null;
                error_log("GREEN! Retorno: {$data['retorno']} > Apostado: {$data['valor_apostado']}");
            } else {
                // PERDEU (Red)
                $data['red'] = 0; // Perda total
                $data['green'] = null;
                error_log("RED! Retorno: {$data['retorno']} <= Apostado: {$data['valor_apostado']}");
            }
        }
        
        return $data;
    }
    
    private function normalizeText($text) {
        // Preserva números com pontos/vírgulas
        $text = strtolower($text);
        // Remove múltiplos espaços mas preserva números
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text);
    }
    
    private function parseNumber($value) {
        $value = trim($value);
        // Remove R$ e espaços
        $value = str_replace(['R$', 'r$', ' '], '', $value);
        
        // Substitui vírgula por ponto
        $value = str_replace(',', '.', $value);
        
        // Remove pontos de milhar (se houver mais de um ponto)
        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $decimal = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimal;
        }
        
        $result = floatval($value);
        error_log("Parse: '$value' => $result");
        return $result;
    }
}