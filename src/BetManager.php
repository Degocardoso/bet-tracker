<?php
namespace App;

class BetManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function saveBet($data) {
        // Define status baseado se tem resultado ou não
        $status = 'em_aberto';
        if ($data['green'] !== null || $data['red'] !== null) {
            $status = $data['green'] ? 'green' : 'red';
        }
        
        $sql = "INSERT INTO bets (data, valor_apostado, odd, green, red, usuario, imagem_nome, status) 
                VALUES (:data, :valor_apostado, :odd, :green, :red, :usuario, :imagem_nome, :status)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':data' => $data['data'],
            ':valor_apostado' => $data['valor_apostado'],
            ':odd' => $data['odd'],
            ':green' => $data['green'],
            ':red' => $data['red'],
            ':usuario' => $data['usuario'],
            ':imagem_nome' => $data['imagem_nome'] ?? null,
            ':status' => $status
        ]);
    }
    
    public function updateBetResult($id, $retorno, $usuario) {
        // Primeiro verifica se a aposta pertence ao usuário
        $bet = $this->getBet($id);
        if (!$bet || $bet['usuario'] !== $usuario) {
            return false; // Não pode editar aposta de outro usuário
        }
        
        // Calcula Green ou Red
        $green = null;
        $red = null;
        $status = 'em_aberto';
        
        if ($retorno > $bet['valor_apostado']) {
            $green = $retorno;
            $status = 'green';
        } else {
            $red = 0;
            $status = 'red';
        }
        
        $sql = "UPDATE bets SET green = :green, red = :red, status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':id' => $id,
            ':green' => $green,
            ':red' => $red,
            ':status' => $status
        ]);
    }
    
    public function getAllBets($usuario = null, $dataInicio = null, $dataFim = null, $status = null) {
        $sql = "SELECT * FROM bets WHERE 1=1";
        $params = [];
        
        if ($usuario) {
            $sql .= " AND usuario = :usuario";
            $params[':usuario'] = $usuario;
        }
        
        if ($dataInicio) {
            $sql .= " AND data >= :data_inicio";
            $params[':data_inicio'] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND data <= :data_fim";
            $params[':data_fim'] = $dataFim;
        }
        
        if ($status) {
            $sql .= " AND status = :status";
            $params[':status'] = $status;
        }
        
        $sql .= " ORDER BY data DESC, created_at DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }
    
    public function deleteBet($id) {
        $sql = "DELETE FROM bets WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }
    
    public function getBet($id) {
        $sql = "SELECT * FROM bets WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function getUsuarios() {
        $sql = "SELECT DISTINCT usuario FROM bets ORDER BY usuario";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }
    
    public function getStatistics($usuario = null) {
        $sql = "SELECT 
                    COUNT(*) as total_apostas,
                    SUM(valor_apostado) as total_investido,
                    SUM(CASE WHEN green IS NOT NULL THEN green ELSE 0 END) as total_ganho,
                    SUM(CASE WHEN red IS NOT NULL THEN valor_apostado ELSE 0 END) as total_perdido,
                    COUNT(CASE WHEN status = 'green' THEN 1 END) as total_greens,
                    COUNT(CASE WHEN status = 'red' THEN 1 END) as total_reds,
                    COUNT(CASE WHEN status = 'em_aberto' THEN 1 END) as total_em_aberto
                FROM bets";
        
        $params = [];
        if ($usuario) {
            $sql .= " WHERE usuario = :usuario";
            $params[':usuario'] = $usuario;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        $stats['total_investido'] = floatval($stats['total_investido'] ?? 0);
        $stats['total_ganho'] = floatval($stats['total_ganho'] ?? 0);
        $stats['total_perdido'] = floatval($stats['total_perdido'] ?? 0);
        $stats['saldo'] = $stats['total_ganho'] - $stats['total_investido'];
        $stats['roi'] = $stats['total_investido'] > 0 
            ? (($stats['saldo'] / $stats['total_investido']) * 100) 
            : 0;
        
        return $stats;
    }
    
    public function getStatisticsByUser() {
        $sql = "SELECT 
                    usuario,
                    COUNT(*) as total_apostas,
                    SUM(valor_apostado) as total_investido,
                    SUM(CASE WHEN green IS NOT NULL THEN green ELSE 0 END) as total_ganho,
                    COUNT(CASE WHEN status = 'green' THEN 1 END) as total_greens,
                    COUNT(CASE WHEN status = 'red' THEN 1 END) as total_reds,
                    COUNT(CASE WHEN status = 'em_aberto' THEN 1 END) as total_em_aberto
                FROM bets
                GROUP BY usuario
                ORDER BY usuario";
        
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll();
        
        foreach ($results as &$user) {
            $user['total_investido'] = floatval($user['total_investido'] ?? 0);
            $user['total_ganho'] = floatval($user['total_ganho'] ?? 0);
            $user['saldo'] = $user['total_ganho'] - $user['total_investido'];
            $user['roi'] = $user['total_investido'] > 0 
                ? (($user['saldo'] / $user['total_investido']) * 100) 
                : 0;
        }
        
        return $results;
    }
}