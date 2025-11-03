<?php
namespace App;

/**
 * Processador OCR alternativo usando Google Cloud Vision API
 * Use esta classe se tiver problemas com o Tesseract no Heroku
 * 
 * Para usar:
 * 1. Crie uma conta no Google Cloud Platform
 * 2. Ative a Cloud Vision API
 * 3. Crie uma chave de API
 * 4. Configure a variável de ambiente: GOOGLE_VISION_API_KEY
 */
class OCRProcessorGoogleVision {
    
    private $apiKey;
    
    public function __construct() {
        $this->apiKey = getenv('GOOGLE_VISION_API_KEY');
        if (!$this->apiKey) {
            throw new \Exception('GOOGLE_VISION_API_KEY não configurada');
        }
    }
    
    public function processImage($imagePath) {
        try {
            // Lê a imagem e converte para base64
            $imageContent = file_get_contents($imagePath);
            $base64Image = base64_encode($imageContent);
            
            // Prepara a requisição para a API
            $url = "https://vision.googleapis.com/v1/images:annotate?key=" . $this->apiKey;
            
            $data = [
                'requests' => [
                    [
                        'image' => [
                            'content' => $base64Image
                        ],
                        'features' => [
                            [
                                'type' => 'TEXT_DETECTION'
                            ]
                        ]
                    ]
                ]
            ];
            
            // Faz a requisição
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json'
            ]);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode !== 200) {
                throw new \Exception("Erro na API do Google Vision: HTTP $httpCode");
            }
            
            $result = json_decode($response, true);
            
            if (isset($result['responses'][0]['textAnnotations'][0]['description'])) {
                $text = $result['responses'][0]['textAnnotations'][0]['description'];
                return $this->extractBetData($text);
            }
            
            return null;
            
        } catch (\Exception $e) {
            error_log("Erro no OCR Google Vision: " . $e->getMessage());
            return null;
        }
    }
    
    private function extractBetData($text) {
        // Mesma lógica de extração do OCRProcessor original
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
            $data['data'] = "$day/$month/$year";
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
