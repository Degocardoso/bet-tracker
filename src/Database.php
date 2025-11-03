<?php
namespace App;

class Database {
    private static $instance = null;
    private $connection;
    
    private function __construct() {
        // Detecta se está no Heroku (usa PostgreSQL) ou local (usa SQLite)
        $databaseUrl = getenv('DATABASE_URL');
        
        if ($databaseUrl) {
            // Parse da URL do Heroku PostgreSQL
            $parsed = parse_url($databaseUrl);
            $host = $parsed['host'];
            $port = $parsed['port'] ?? 5432;
            $dbname = ltrim($parsed['path'], '/');
            $user = $parsed['user'];
            $password = $parsed['pass'];
            
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
            $this->connection = new \PDO($dsn, $user, $password, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        } else {
            // SQLite para desenvolvimento local
            $dbPath = __DIR__ . '/../data/bets.db';
            $dbDir = dirname($dbPath);
            
            if (!is_dir($dbDir)) {
                mkdir($dbDir, 0777, true);
            }
            
            $this->connection = new \PDO("sqlite:$dbPath", null, null, [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC
            ]);
        }
        
        $this->initDatabase();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    private function initDatabase() {
        $sql = "
            CREATE TABLE IF NOT EXISTS bets (
                id INTEGER PRIMARY KEY " . (getenv('DATABASE_URL') ? "" : "AUTOINCREMENT") . ",
                data DATE NOT NULL,
                valor_apostado DECIMAL(10,2) NOT NULL,
                odd DECIMAL(10,2) NOT NULL,
                green DECIMAL(10,2) DEFAULT NULL,
                red DECIMAL(10,2) DEFAULT NULL,
                usuario VARCHAR(100) NOT NULL,
                imagem_nome VARCHAR(255),
                status VARCHAR(20) DEFAULT 'em_aberto',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ";
        
        // Ajusta para PostgreSQL
        if (getenv('DATABASE_URL')) {
            $sql = str_replace('INTEGER PRIMARY KEY', 'SERIAL PRIMARY KEY', $sql);
            $sql = str_replace('AUTOINCREMENT', '', $sql);
        }
        
        $this->connection->exec($sql);
        
        // Adiciona coluna status se não existir (para bancos antigos)
        try {
            if (getenv('DATABASE_URL')) {
                $this->connection->exec("ALTER TABLE bets ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'em_aberto'");
            } else {
                // SQLite não suporta IF NOT EXISTS em ALTER, então tenta e ignora erro
                try {
                    $this->connection->exec("ALTER TABLE bets ADD COLUMN status VARCHAR(20) DEFAULT 'em_aberto'");
                } catch (\Exception $e) {
                    // Coluna já existe, ignora
                }
            }
        } catch (\Exception $e) {
            // Ignora erros
        }
    }
}
