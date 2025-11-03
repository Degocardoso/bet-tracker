<?php
namespace App;

class BetManager {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function saveBet($data) {
        $sql = "INSERT INTO bets (data, valor_apostado, odd, green, red, usuario, imagem_nome) 
                VALUES (:data, :valor_apostado, :odd, :green, :red, :usuario, :imagem_nome)";
        
        $stmt = $this->db->prepare($sql);
        
        return $stmt->execute([
            ':data' => $data['data'],
            ':valor_apostado' => $data['valor_apostado'],
            ':odd' => $data['odd'],
            ':green' => $data['green'],
            ':red' => $data['red'],
            ':usuario' => $data['usuario'],
            ':imagem_nome' => $data['imagem_nome'] ?? null
        ]);
    }
    
    public function getAllBets($usuario = null, $dataInicio = null, $dataFim = null) {
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
                    COUNT(CASE WHEN green IS NOT NULL THEN 1 END) as total_greens,
                    COUNT(CASE WHEN red IS NOT NULL THEN 1 END) as total_reds
                FROM bets";
        
        $params = [];
        if ($usuario) {
            $sql .= " WHERE usuario = :usuario";
            $params[':usuario'] = $usuario;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch();
        
        // Calcula lucro/prejuízo
        $stats['saldo'] = ($stats['total_ganho'] ?? 0) - ($stats['total_investido'] ?? 0);
        $stats['roi'] = $stats['total_investido'] > 0 
            ? (($stats['saldo'] / $stats['total_investido']) * 100) 
            : 0;
        
        return $stats;
    }
}
