<?php
// Teste simples para verificar se o ambiente está configurado corretamente

echo "<h1>🎯 Bet Tracker - Teste de Ambiente</h1>";

// Verifica versão do PHP
echo "<h2>✅ PHP</h2>";
echo "Versão: " . phpversion() . "<br>";
if (version_compare(phpversion(), '8.0.0', '>=')) {
    echo "<span style='color: green;'>✓ PHP 8.0+ instalado</span><br>";
} else {
    echo "<span style='color: red;'>✗ PHP 8.0+ necessário</span><br>";
}

// Verifica extensões
echo "<h2>✅ Extensões PHP</h2>";
$required_extensions = ['pdo', 'gd', 'pdo_sqlite'];
foreach ($required_extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "<span style='color: green;'>✓ $ext</span><br>";
    } else {
        echo "<span style='color: red;'>✗ $ext (não instalada)</span><br>";
    }
}

// Verifica Tesseract
echo "<h2>✅ Tesseract OCR</h2>";
exec('tesseract --version 2>&1', $output, $return_code);
if ($return_code === 0) {
    echo "<span style='color: green;'>✓ Tesseract instalado</span><br>";
    echo "Versão: " . $output[0] . "<br>";
} else {
    echo "<span style='color: red;'>✗ Tesseract não encontrado</span><br>";
    echo "<small>Instale com: sudo apt-get install tesseract-ocr tesseract-ocr-por</small><br>";
}

// Verifica Composer
echo "<h2>✅ Composer</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "<span style='color: green;'>✓ Dependências instaladas</span><br>";
} else {
    echo "<span style='color: red;'>✗ Execute 'composer install'</span><br>";
}

// Verifica diretórios
echo "<h2>✅ Diretórios</h2>";
$directories = [
    'uploads' => __DIR__ . '/uploads',
    'data' => __DIR__ . '/data'
];

foreach ($directories as $name => $path) {
    if (is_dir($path) && is_writable($path)) {
        echo "<span style='color: green;'>✓ $name (gravável)</span><br>";
    } elseif (is_dir($path)) {
        echo "<span style='color: orange;'>⚠ $name (não gravável)</span><br>";
    } else {
        echo "<span style='color: red;'>✗ $name (não existe)</span><br>";
    }
}

// Verifica banco de dados
echo "<h2>✅ Banco de Dados</h2>";
try {
    if (getenv('DATABASE_URL')) {
        echo "<span style='color: green;'>✓ PostgreSQL (Heroku)</span><br>";
    } else {
        $dbPath = __DIR__ . '/data/bets.db';
        echo "<span style='color: green;'>✓ SQLite (Local)</span><br>";
        echo "Path: $dbPath<br>";
    }
} catch (Exception $e) {
    echo "<span style='color: red;'>✗ Erro no banco: " . $e->getMessage() . "</span><br>";
}

echo "<hr>";
echo "<h2>🚀 Status Final</h2>";
echo "<p>Se todos os itens estão em verde, o sistema está pronto para usar!</p>";
echo "<p><a href='index.php'>← Voltar para o sistema</a></p>";

echo "<style>
    body { font-family: Arial, sans-serif; padding: 20px; max-width: 800px; margin: 0 auto; }
    h1 { color: #667eea; }
    h2 { color: #333; margin-top: 20px; }
    span { font-weight: bold; }
</style>";
?>
