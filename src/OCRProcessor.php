<?php
namespace App;

class OCRProcessor {
    
    public function processImage($imagePath) {
        try {
            // Verifica se Tesseract está disponível
            if (class_exists('thiagoalessio\TesseractOCR\TesseractOCR')) {
                return $this->processWithTesseract($imagePath);
            } else {
                // Fallback: retorna dados vazios para o usuário preencher manualmente
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
            'valor_apostado' => 0,
            'odd' => 0,
            'green' => null,
            'red' => null
        ];
    }
    
    private function extractBetData($text) {
        $data = [
            'data' => null,
            'valor_apostado' => null,
            'odd' => null,
            'green' => null,
            'red' => null
        ];
        
        $text = $this->normalizeText($text);
        
        // Extrai DATA
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', $text, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
            $data['data'] = "$year-$month-$day";
        }
        
        // Extrai VALOR APOSTADO
        if (preg_match('/(?:valor|aposta|investido|apostado)[:\s]*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
        } elseif (preg_match('/r\$\s*(\d+[,\.]?\d*)/', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai ODD
        if (preg_match('/(?:odd|cotacao|cotação|@)[:\s]*(\d+[,\.]?\d*)/', $text, $matches)) {
            $data['odd'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai RETORNO
        $retorno = null;
        if (preg_match('/(?:retorno|ganho|lucro|premio|prêmio)[:\s]*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $retorno = $this->parseNumber($matches[1]);
        }
        
        // Calcula Green ou Red
        if ($retorno !== null && $data['valor_apostado'] !== null) {
            if ($retorno > $data['valor_apostado']) {
                $data['green'] = $retorno;
            } else {
                $data['red'] = $data['valor_apostado'] - $retorno;
            }
        }
        
        if (preg_match('/green|vit[oó]ria|ganhou/i', $text) && $data['valor_apostado'] && $data['odd']) {
            $data['green'] = $data['valor_apostado'] * $data['odd'];
        }
        
        if (preg_match('/red|derrota|perdeu/i', $text) && $data['valor_apostado']) {
            $data['red'] = 0;
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
        $value = str_replace(',', '.', $value);
        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $decimal = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimal;
        }
        return floatval($value);
    }
}