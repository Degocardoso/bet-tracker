<?php
namespace App;

use thiagoalessio\TesseractOCR\TesseractOCR;

class OCRProcessor {
    
    public function processImage($imagePath) {
        try {
            // Processa a imagem com Tesseract OCR
            $text = (new TesseractOCR($imagePath))
                ->lang('por') // Português
                ->run();
            
            return $this->extractBetData($text);
        } catch (\Exception $e) {
            error_log("Erro no OCR: " . $e->getMessage());
            return null;
        }
    }
    
    private function extractBetData($text) {
        $data = [
            'data' => null,
            'valor_apostado' => null,
            'odd' => null,
            'green' => null,
            'red' => null
        ];
        
        // Remove caracteres especiais e normaliza o texto
        $text = $this->normalizeText($text);
        
        // Extrai DATA (formatos: dd/mm/yyyy, dd-mm-yyyy, dd.mm.yyyy)
        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{2,4})/', $text, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($matches[2], 2, '0', STR_PAD_LEFT);
            $year = strlen($matches[3]) == 2 ? '20' . $matches[3] : $matches[3];
            $data['data'] = "$day/$month/$year";
        }
        
        // Extrai VALOR APOSTADO
        // Procura por padrões como: "R$ 50", "valor: 50", "aposta: 50"
        if (preg_match('/(?:valor|aposta|investido|apostado)[:\s]*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
        } elseif (preg_match('/r\$\s*(\d+[,\.]?\d*)/', $text, $matches)) {
            $data['valor_apostado'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai ODD
        // Procura por padrões como: "odd: 2.10", "cotação: 2.10", "@2.10"
        if (preg_match('/(?:odd|cotacao|cotação|@)[:\s]*(\d+[,\.]?\d*)/', $text, $matches)) {
            $data['odd'] = $this->parseNumber($matches[1]);
        }
        
        // Extrai RETORNO/GANHO (para calcular Green ou Red)
        $retorno = null;
        if (preg_match('/(?:retorno|ganho|lucro|premio|prêmio)[:\s]*r?\$?\s*(\d+[,\.]?\d*)/i', $text, $matches)) {
            $retorno = $this->parseNumber($matches[1]);
        }
        
        // Verifica se é GREEN ou RED
        if ($retorno !== null && $data['valor_apostado'] !== null) {
            if ($retorno > $data['valor_apostado']) {
                $data['green'] = $retorno;
            } else {
                $data['red'] = $data['valor_apostado'] - $retorno;
                if ($data['red'] == $data['valor_apostado']) {
                    $data['red'] = 0; // Perda total
                }
            }
        }
        
        // Se encontrou "green" ou "vitória" no texto
        if (preg_match('/green|vit[oó]ria|ganhou/i', $text) && $data['valor_apostado'] && $data['odd']) {
            $data['green'] = $data['valor_apostado'] * $data['odd'];
        }
        
        // Se encontrou "red" ou "derrota" no texto
        if (preg_match('/red|derrota|perdeu/i', $text) && $data['valor_apostado']) {
            $data['red'] = 0;
        }
        
        return $data;
    }
    
    private function normalizeText($text) {
        // Remove acentos para facilitar busca
        $text = strtolower($text);
        // Remove quebras de linha e espaços extras
        $text = preg_replace('/\s+/', ' ', $text);
        return $text;
    }
    
    private function parseNumber($value) {
        // Remove espaços
        $value = trim($value);
        // Substitui vírgula por ponto
        $value = str_replace(',', '.', $value);
        // Remove pontos de milhar (se houver mais de um ponto)
        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $decimal = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimal;
        }
        return floatval($value);
    }
}
