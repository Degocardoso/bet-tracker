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
            'green' => null,
            'red' => null
        ];
    }
    
    private function extractBetData($text) {
        $data = [
            'data' => date('Y-m-d'),
            'valor_apostado' => '',
            'odd' => '',
            'green' => null,
            'red' => null
        ];
        
        $text = $this->normalizeText($text);
        
        // Extrai DATA no formato dd/mm/yyyy
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $text, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $year = $matches[3];
            $data['data'] = "$year-$month-$day";
        }
        
        // Extrai VALOR APOSTADO
        // Padrão: "Aposta R$130,00" ou "Aposta 130.00"
        if (preg_match('/aposta\s*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai ODD
        // Busca número decimal isolado (1.68, 2.10, etc)
        // Ou após palavra "odd", "cotação", etc
        if (preg_match('/(?:odd|cotacao|cotação)[:\s]*(\d+[,\.]\d+)/i', $text, $matches)) {
            $data['odd'] = $this->parseNumber($matches[1]);
        } elseif (preg_match('/\b(\d{1},\d{2})\b/', $text, $matches)) {
            // Busca padrão X,XX (como 1,68)
            $data['odd'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai RETORNO
        // Padrão: "Retorno R$219,66"
        if (preg_match('/retorno\s*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $retorno = $this->parseNumber($matches[1]);
            
            // Se o retorno é maior que o apostado, é GREEN
            if ($retorno > 0 && $data['valor_apostado'] > 0) {
                if ($retorno > $data['valor_apostado']) {
                    $data['green'] = $retorno;
                    $data['red'] = null;
                } else {
                    $data['red'] = 0;
                    $data['green'] = null;
                }
            }
        }
        
        // Verifica palavras-chave
        if (preg_match('/green|ganhou|vit[oó]ria|venceu/i', $text)) {
            if ($data['valor_apostado'] && $data['odd']) {
                $data['green'] = $data['valor_apostado'] * $data['odd'];
                $data['red'] = null;
            }
        }
        
        if (preg_match('/red|perdeu|derrota/i', $text)) {
            $data['red'] = 0;
            $data['green'] = null;
        }
        
        return $data;
    }
    
    private function normalizeText($text) {
        $text = strtolower($text);
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }
    
    private function parseNumber($value) {
        $value = trim($value);
        // Remove R$ e espaços
        $value = str_replace(['R$', ' '], '', $value);
        // Substitui vírgula por ponto
        $value = str_replace(',', '.', $value);
        // Remove pontos de milhar
        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $decimal = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimal;
        }
        return floatval($value);
    }
}