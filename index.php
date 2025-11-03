<?php
require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\BetManager;
use App\OCRProcessor;
use App\ExcelExporter;

session_start();

// Inicializa o gerenciador de apostas
$betManager = new BetManager();

// Processa ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Upload de imagem
    if (isset($_POST['action']) && $_POST['action'] === 'upload') {
        $usuario = trim($_POST['usuario'] ?? '');
        
        if (empty($usuario)) {
            $message = 'Por favor, informe seu nome.';
            $messageType = 'danger';
        } elseif (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['imagem']['name']);
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $filepath)) {
                // Processa OCR
                $ocrProcessor = new OCRProcessor();
                $extractedData = $ocrProcessor->processImage($filepath);
                
                if ($extractedData) {
                    // Adiciona o usuário aos dados
                    $extractedData['usuario'] = $usuario;
                    $extractedData['imagem_nome'] = $filename;
                    
                    // Salva no banco
                    if ($betManager->saveBet($extractedData)) {
                        $message = 'Aposta cadastrada com sucesso!';
                        $messageType = 'success';
                    } else {
                        $message = 'Erro ao salvar a aposta.';
                        $messageType = 'danger';
                    }
                } else {
                    $message = 'Não foi possível extrair dados da imagem. Por favor, verifique a qualidade da imagem.';
                    $messageType = 'warning';
                }
            } else {
                $message = 'Erro ao fazer upload da imagem.';
                $messageType = 'danger';
            }
        } else {
            $message = 'Por favor, selecione uma imagem.';
            $messageType = 'danger';
        }
    }
    
    // Exclusão de aposta
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $id = intval($_POST['id']);
        if ($betManager->deleteBet($id)) {
            $message = 'Aposta excluída com sucesso!';
            $messageType = 'success';
        } else {
            $message = 'Erro ao excluir a aposta.';
            $messageType = 'danger';
        }
    }
}

// Exportação
if (isset($_GET['export'])) {
    $usuario = $_GET['usuario'] ?? null;
    $dataInicio = $_GET['data_inicio'] ?? null;
    $dataFim = $_GET['data_fim'] ?? null;
    
    $bets = $betManager->getAllBets($usuario, $dataInicio, $dataFim);
    $exporter = new ExcelExporter();
    
    if ($_GET['export'] === 'excel') {
        $file = $exporter->exportBets($bets);
    } else {
        $file = $exporter->exportCSV($bets);
    }
    
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $file['filename'] . '"');
    header('Content-Length: ' . filesize($file['filepath']));
    readfile($file['filepath']);
    unlink($file['filepath']);
    exit;
}

// Busca apostas com filtros
$usuario = $_GET['usuario'] ?? null;
$dataInicio = $_GET['data_inicio'] ?? null;
$dataFim = $_GET['data_fim'] ?? null;

$bets = $betManager->getAllBets($usuario, $dataInicio, $dataFim);
$usuarios = $betManager->getUsuarios();
$stats = $betManager->getStatistics($usuario);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bet Tracker - Sistema de Rastreamento de Apostas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --green-color: #28a745;
            --red-color: #dc3545;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        
        .main-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin-bottom: 30px;
        }
        
        .upload-area {
            border: 3px dashed #667eea;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .upload-area:hover {
            border-color: #764ba2;
            background: #e9ecef;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        .stats-card h4 {
            font-size: 2rem;
            margin-bottom: 5px;
        }
        
        .table-green {
            background-color: #d4edda !important;
        }
        
        .table-red {
            background-color: #f8d7da !important;
        }
        
        .btn-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
        }
        
        .btn-custom:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1 class="display-4"><i class="bi bi-trophy"></i> Bet Tracker</h1>
            <p class="lead">Sistema Inteligente de Rastreamento de Apostas com OCR</p>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estatísticas -->
        <div class="row mb-4">
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <h4><?php echo $stats['total_apostas']; ?></h4>
                    <p class="mb-0">Total de Apostas</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card">
                    <h4>R$ <?php echo number_format($stats['total_investido'], 2, ',', '.'); ?></h4>
                    <p class="mb-0">Total Investido</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card" style="background: var(--green-color);">
                    <h4><?php echo $stats['total_greens']; ?></h4>
                    <p class="mb-0">Greens</p>
                </div>
            </div>
            <div class="col-md-3 col-6 mb-3">
                <div class="stats-card" style="background: var(--red-color);">
                    <h4><?php echo $stats['total_reds']; ?></h4>
                    <p class="mb-0">Reds</p>
                </div>
            </div>
        </div>

        <!-- Upload -->
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-cloud-upload"></i> Cadastrar Nova Aposta</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="upload">
                
                <div class="mb-3">
                    <label class="form-label">Seu Nome *</label>
                    <input type="text" name="usuario" class="form-control" required placeholder="Digite seu nome">
                </div>
                
                <div class="upload-area mb-3">
                    <i class="bi bi-image" style="font-size: 3rem; color: #667eea;"></i>
                    <h5 class="mt-3">Envie o print da sua aposta</h5>
                    <p class="text-muted">Arraste e solte ou clique para selecionar</p>
                    <input type="file" name="imagem" class="form-control mt-3" accept="image/*" required>
                </div>
                
                <button type="submit" class="btn btn-custom btn-lg w-100">
                    <i class="bi bi-magic"></i> Processar com OCR
                </button>
            </form>
        </div>

        <!-- Filtros -->
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-filter"></i> Filtros</h3>
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Usuário</label>
                    <select name="usuario" class="form-select">
                        <option value="">Todos</option>
                        <?php foreach ($usuarios as $u): ?>
                            <option value="<?php echo htmlspecialchars($u); ?>" <?php echo $usuario === $u ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($dataInicio ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($dataFim ?? ''); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-custom w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabela de Apostas -->
        <div class="main-card p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="bi bi-table"></i> Relatório de Apostas</h3>
                <div class="btn-group">
                    <a href="?export=excel<?php echo $usuario ? '&usuario=' . urlencode($usuario) : ''; ?><?php echo $dataInicio ? '&data_inicio=' . $dataInicio : ''; ?><?php echo $dataFim ? '&data_fim=' . $dataFim : ''; ?>" 
                       class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Excel
                    </a>
                    <a href="?export=csv<?php echo $usuario ? '&usuario=' . urlencode($usuario) : ''; ?><?php echo $dataInicio ? '&data_inicio=' . $dataInicio : ''; ?><?php echo $dataFim ? '&data_fim=' . $dataFim : ''; ?>" 
                       class="btn btn-info">
                        <i class="bi bi-filetype-csv"></i> CSV
                    </a>
                </div>
            </div>

            <?php if (empty($bets)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Nenhuma aposta encontrada. Faça o upload do primeiro print!
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Data</th>
                                <th>Valor Apostado</th>
                                <th>ODD</th>
                                <th>Green</th>
                                <th>Red</th>
                                <th>Usuário</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bets as $bet): ?>
                            <tr class="<?php echo $bet['green'] ? 'table-green' : ($bet['red'] !== null ? 'table-red' : ''); ?>">
                                <td><?php echo htmlspecialchars($bet['data']); ?></td>
                                <td>R$ <?php echo number_format($bet['valor_apostado'], 2, ',', '.'); ?></td>
                                <td><?php echo number_format($bet['odd'], 2, ',', '.'); ?></td>
                                <td>
                                    <?php echo $bet['green'] ? 'R$ ' . number_format($bet['green'], 2, ',', '.') : '-'; ?>
                                </td>
                                <td>
                                    <?php echo $bet['red'] !== null ? 'R$ ' . number_format($bet['red'], 2, ',', '.') : '-'; ?>
                                </td>
                                <td><?php echo htmlspecialchars($bet['usuario']); ?></td>
                                <td>
                                    <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?php echo $bet['id']; ?>)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Footer -->
        <div class="text-center text-white mt-4">
            <p>&copy; 2025 Bet Tracker - Desenvolvido com ❤️</p>
        </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    Tem certeza que deseja excluir esta aposta? Esta ação não pode ser desfeita.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form method="POST" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteId">
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarExclusao(id) {
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
