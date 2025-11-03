<?php
namespace App;

class UserManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->initDatabase();
    }
    
    private function initDatabase() {
        $isPostgres = getenv('DATABASE_URL') !== false;
        
        if ($isPostgres) {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id SERIAL PRIMARY KEY,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nome_completo VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                ativo BOOLEAN DEFAULT TRUE,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        } else {
            $sql = "CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username VARCHAR(50) UNIQUE NOT NULL,
                password VARCHAR(255) NOT NULL,
                nome_completo VARCHAR(100) NOT NULL,
                email VARCHAR(100),
                ativo INTEGER DEFAULT 1,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )";
        }
        
        $this->db->exec($sql);
        $this->createDefaultAdmin();
    }
    
    private function createDefaultAdmin() {
        try {
            $sql = "SELECT COUNT(*) as total FROM users WHERE username = :username";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':username' => 'admin']);
            $result = $stmt->fetch();
            
            if ($result['total'] == 0) {
                // Nova senha: sHMRe?!<<_886wj$R4ag
                $this->createUser('admin', 'sHMRe?!<<_886wj$R4ag', 'Administrador', 'admin@bettracker.com');
            }
        } catch (\Exception $e) {
            // Ignora se tabela não existe
        }
    }
    
    public function createUser($username, $password, $nomeCompleto, $email = null) {
        $sql = "INSERT INTO users (username, password, nome_completo, email, ativo) 
                VALUES (:username, :password, :nome_completo, :email, :ativo)";
        
        $stmt = $this->db->prepare($sql);
        $isPostgres = getenv('DATABASE_URL') !== false;
        $ativo = $isPostgres ? true : 1;
        
        return $stmt->execute([
            ':username' => $username,
            ':password' => password_hash($password, PASSWORD_DEFAULT),
            ':nome_completo' => $nomeCompleto,
            ':email' => $email,
            ':ativo' => $ativo
        ]);
    }
    
    public function login($username, $password) {
        $isPostgres = getenv('DATABASE_URL') !== false;
        $ativoValue = $isPostgres ? 'TRUE' : '1';
        
        $sql = "SELECT * FROM users WHERE username = :username AND ativo = {$ativoValue}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':username' => $username]);
        
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }
    
    public function getUserById($id) {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getAllUsers() {
        $sql = "SELECT id, username, nome_completo, email, ativo, created_at FROM users ORDER BY nome_completo";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function updateUser($id, $nomeCompleto, $email) {
        $sql = "UPDATE users SET nome_completo = :nome_completo, email = :email WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nome_completo' => $nomeCompleto,
            ':email' => $email
        ]);
    }
    
    public function changePassword($id, $newPassword) {
        $sql = "UPDATE users SET password = :password WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':password' => password_hash($newPassword, PASSWORD_DEFAULT)
        ]);
    }
    
    public function toggleUserStatus($id) {
        $sql = "UPDATE users SET ativo = NOT ativo WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function deleteUser($id) {
        if ($id == 1) {
            return false;
        }
        
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
}