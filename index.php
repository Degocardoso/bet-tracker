<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;
use App\BetManager;
use App\OCRProcessor;
use App\ExcelExporter;

$betManager = new BetManager();
$usuarioLogado = $_SESSION['nome_completo'];
$isAdmin = $_SESSION['username'] === 'admin';

$message = '';
$messageType = '';
$ocrData = null;
$uploadedImage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Cadastro manual - vai direto para modal de confirmação
    if (isset($_POST['action']) && $_POST['action'] === 'manual_preview') {
        $ocrData = [
            'data' => $_POST['data'] ?? date('Y-m-d'),
            'valor_apostado' => floatval($_POST['valor_apostado'] ?? 0),
            'odd' => floatval($_POST['odd'] ?? 0),
            'retorno' => floatval($_POST['retorno'] ?? 0),
            'green' => null,
            'red' => null
        ];
        
        // Calcula Green ou Red
        if ($ocrData['retorno'] > $ocrData['valor_apostado']) {
            $ocrData['green'] = $ocrData['retorno'];
        } else {
            $ocrData['red'] = 0;
        }
        
        $uploadedImage = null; // Sem imagem no modo manual
    }
    
    // Upload inicial - só processa OCR
    if (isset($_POST['action']) && $_POST['action'] === 'upload_preview') {
        if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $filename = uniqid() . '_' . basename($_FILES['imagem']['name']);
            $filepath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['imagem']['tmp_name'], $filepath)) {
                $ocrProcessor = new OCRProcessor();
                $ocrData = $ocrProcessor->processImage($filepath);
                $uploadedImage = $filename;
            }
        }
    }
    
    // Salvar após confirmação
    if (isset($_POST['action']) && $_POST['action'] === 'save_bet') {
        $valorApostado = floatval($_POST['valor_apostado'] ?? 0);
        $retorno = floatval($_POST['retorno'] ?? 0);
        
        // Verifica se é aposta em aberto
        $semResultado = isset($_POST['sem_resultado']) && $_POST['sem_resultado'] === 'on';
        
        $green = null;
        $red = null;
        
        if (!$semResultado && $retorno > 0) {
            if ($retorno > $valorApostado) {
                $green = $retorno;
            } else {
                $red = 0;
            }
        }
        
        $betData = [
            'data' => $_POST['data'] ?? date('Y-m-d'),
            'valor_apostado' => $valorApostado,
            'odd' => floatval($_POST['odd'] ?? 0),
            'green' => $green,
            'red' => $red,
            'usuario' => $usuarioLogado,
            'imagem_nome' => $_POST['imagem_nome'] ?? null
        ];
        
        if ($betManager->saveBet($betData)) {
            $message = 'Aposta cadastrada com sucesso!';
            $messageType = 'success';
        } else {
            $message = 'Erro ao salvar a aposta.';
            $messageType = 'danger';
        }
    }
    
    // Fechar aposta (só quem criou)
    if (isset($_POST['action']) && $_POST['action'] === 'fechar_aposta') {
        $id = intval($_POST['id']);
        $retorno = floatval($_POST['retorno']);
        
        if ($betManager->updateBetResult($id, $retorno, $usuarioLogado)) {
            $message = 'Aposta fechada com sucesso!';
            $messageType = 'success';
        } else {
            $message = 'Erro: Você só pode fechar suas próprias apostas.';
            $messageType = 'danger';
        }
    }
    
    // Exclusão (só admin)
    if (isset($_POST['action']) && $_POST['action'] === 'delete' && $isAdmin) {
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

$usuario = $_GET['usuario'] ?? null;
$dataInicio = $_GET['data_inicio'] ?? null;
$dataFim = $_GET['data_fim'] ?? null;
$statusFiltro = $_GET['status'] ?? null;

$bets = $betManager->getAllBets($usuario, $dataInicio, $dataFim, $statusFiltro);
$usuarios = $betManager->getUsuarios();
$stats = $betManager->getStatistics($usuario);
$statsByUser = $betManager->getStatisticsByUser();
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
        
        .stats-card.lucro {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        
        .stats-card.prejuizo {
            background: linear-gradient(135deg, #dc3545 0%, #e85d75 100%);
        }
        
        .table-green {
            background-color: #d4edda !important;
        }
        
        .table-red {
            background-color: #f8d7da !important;
        }
        
        .table-warning {
            background-color: #fff3cd !important;
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
        
        .user-info {
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 10px;
            color: white;
        }
        
        .preview-image {
            max-width: 100%;
            max-height: 300px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        
        .modal-xl {
            max-width: 900px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center text-white mb-4">
            <div>
                <h1 class="display-4"><i class="bi bi-trophy"></i> Bet Tracker</h1>
                <p class="lead">Sistema Inteligente de Rastreamento de Apostas</p>
            </div>
            <div class="user-info">
                <i class="bi bi-person-circle"></i> <strong><?php echo htmlspecialchars($usuarioLogado); ?></strong>
                <?php if ($isAdmin): ?>
                <a href="admin.php" class="btn btn-sm btn-warning ms-2">
                    <i class="bi bi-gear"></i> Admin
                </a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-sm btn-danger ms-2">
                    <i class="bi bi-box-arrow-right"></i> Sair
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Estatísticas Gerais -->
        <div class="row mb-4">
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h4><?php echo intval($stats['total_apostas'] ?? 0); ?></h4>
                    <p class="mb-0">Total de Apostas</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card">
                    <h4>R$ <?php echo number_format(floatval($stats['total_investido'] ?? 0), 2, ',', '.'); ?></h4>
                    <p class="mb-0">Total Investido</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card" style="background: var(--green-color);">
                    <h4><?php echo intval($stats['total_greens'] ?? 0); ?></h4>
                    <p class="mb-0">Greens</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card" style="background: var(--red-color);">
                    <h4><?php echo intval($stats['total_reds'] ?? 0); ?></h4>
                    <p class="mb-0">Reds</p>
                </div>
            </div>
            <div class="col-md-2 col-6 mb-3">
                <div class="stats-card" style="background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);">
                    <h4><?php echo intval($stats['total_em_aberto'] ?? 0); ?></h4>
                    <p class="mb-0">Em Aberto</p>
                </div>
            </div>
        </div>

        <!-- Card de Lucro/Prejuízo Total -->
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="stats-card <?php echo $stats['saldo'] >= 0 ? 'lucro' : 'prejuizo'; ?>">
                    <h5><i class="bi bi-wallet2"></i> SALDO TOTAL</h5>
                    <h3>R$ <?php echo number_format(abs($stats['saldo']), 2, ',', '.'); ?></h3>
                    <p class="mb-0">
                        <?php if ($stats['saldo'] >= 0): ?>
                            <i class="bi bi-arrow-up-circle"></i> LUCRO
                        <?php else: ?>
                            <i class="bi bi-arrow-down-circle"></i> PREJUÍZO
                        <?php endif; ?>
                        (ROI: <?php echo number_format($stats['roi'], 2, ',', '.'); ?>%)
                    </p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="stats-card">
                    <h5><i class="bi bi-graph-up"></i> RETORNO TOTAL</h5>
                    <h3>R$ <?php echo number_format($stats['total_ganho'], 2, ',', '.'); ?></h3>
                    <p class="mb-0">Total ganho em apostas vencedoras</p>
                </div>
            </div>
        </div>

        <!-- Estatísticas por Usuário -->
        <?php if (count($statsByUser) > 1): ?>
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-people"></i> Desempenho por Usuário</h3>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Usuário</th>
                            <th>Apostas</th>
                            <th>Investido</th>
                            <th>Retorno</th>
                            <th>Saldo</th>
                            <th>ROI %</th>
                            <th>Greens</th>
                            <th>Reds</th>
                            <th>Em Aberto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($statsByUser as $userStat): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($userStat['usuario']); ?></strong></td>
                            <td><?php echo intval($userStat['total_apostas']); ?></td>
                            <td>R$ <?php echo number_format($userStat['total_investido'], 2, ',', '.'); ?></td>
                            <td>R$ <?php echo number_format($userStat['total_ganho'], 2, ',', '.'); ?></td>
                            <td class="<?php echo $userStat['saldo'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong>R$ <?php echo number_format(abs($userStat['saldo']), 2, ',', '.'); ?></strong>
                                <?php if ($userStat['saldo'] >= 0): ?>
                                    <i class="bi bi-arrow-up-circle text-success"></i>
                                <?php else: ?>
                                    <i class="bi bi-arrow-down-circle text-danger"></i>
                                <?php endif; ?>
                            </td>
                            <td class="<?php echo $userStat['roi'] >= 0 ? 'text-success' : 'text-danger'; ?>">
                                <strong><?php echo number_format($userStat['roi'], 2, ',', '.'); ?>%</strong>
                            </td>
                            <td><span class="badge bg-success"><?php echo intval($userStat['total_greens']); ?></span></td>
                            <td><span class="badge bg-danger"><?php echo intval($userStat['total_reds']); ?></span></td>
                            <td><span class="badge bg-warning text-dark"><?php echo intval($userStat['total_em_aberto']); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upload -->
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-cloud-upload"></i> Cadastrar Nova Aposta</h3>
            
            <div class="alert alert-info mb-4">
                <i class="bi bi-info-circle"></i> Suas apostas serão automaticamente registradas como: <strong><?php echo htmlspecialchars($usuarioLogado); ?></strong>
            </div>

            <!-- Seletor de Método -->
            <div class="mb-4">
                <label class="form-label fw-bold">Como deseja cadastrar a aposta?</label>
                <div class="btn-group w-100" role="group">
                    <input type="radio" class="btn-check" name="metodo_cadastro" id="metodo_manual" value="manual" checked autocomplete="off">
                    <label class="btn btn-outline-primary" for="metodo_manual" onclick="mostrarFormulario('manual')">
                        <i class="bi bi-pencil-square"></i> Cadastro Manual
                    </label>

                    <input type="radio" class="btn-check" name="metodo_cadastro" id="metodo_imagem" value="imagem" autocomplete="off">
                    <label class="btn btn-outline-primary" for="metodo_imagem" onclick="mostrarFormulario('imagem')">
                        <i class="bi bi-image"></i> Upload de Imagem (OCR)
                    </label>
                </div>
            </div>

            <!-- Formulário Manual -->
            <div id="form_manual" style="display: block;">
                <form method="POST" id="manualForm">
                    <input type="hidden" name="action" value="manual_preview">
                    
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Data da Aposta *</label>
                            <input type="date" name="data" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Valor Apostado (R$) *</label>
                            <input type="number" name="valor_apostado" id="manual_valor" class="form-control" step="0.01" required placeholder="130.00">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">ODD *</label>
                            <input type="number" name="odd" class="form-control" step="0.01" required placeholder="1.68">
                        </div>
                        
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Retorno (R$)</label>
                            <input type="number" name="retorno" id="manual_retorno" class="form-control" step="0.01" placeholder="219.66">
                            <small class="text-muted">Deixe vazio se ainda não fechou</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sem_resultado" name="sem_resultado">
                            <label class="form-check-label" for="sem_resultado">
                                <strong>Aposta ainda está em aberto</strong> (sem resultado ainda)
                            </label>
                        </div>
                        <small class="text-muted">Marque esta opção se a aposta ainda não fechou. Você poderá adicionar o resultado depois.</small>
                    </div>

                    <!-- Preview do resultado em tempo real -->
                    <div class="alert alert-info" id="manual-resultado-preview">
                        <div id="manual-resultado-texto">
                            <i class="bi bi-calculator"></i> Preencha os valores para ver o resultado
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-custom btn-lg w-100">
                        <i class="bi bi-check-circle"></i> Revisar e Confirmar Dados
                    </button>
                </form>
            </div>

            <!-- Formulário Upload de Imagem -->
            <div id="form_imagem" style="display: none;">
                <form method="POST" enctype="multipart/form-data" id="uploadForm">
                    <input type="hidden" name="action" value="upload_preview">
                    
                    <div class="upload-area mb-3">
                        <i class="bi bi-image" style="font-size: 3rem; color: #667eea;"></i>
                        <h5 class="mt-3">Envie o print da sua aposta</h5>
                        <p class="text-muted">Arraste e solte ou clique para selecionar</p>
                        <input type="file" name="imagem" class="form-control mt-3" accept="image/*">
                    </div>
                    
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i> 
                        <strong>Atenção:</strong> O OCR pode não extrair os dados perfeitamente. Você poderá revisar e corrigir antes de salvar.
                    </div>
                    
                    <button type="submit" class="btn btn-custom btn-lg w-100">
                        <i class="bi bi-search"></i> Processar Imagem com OCR
                    </button>
                </form>
            </div>
        </div>

        <!-- Filtros -->
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-filter"></i> Filtros</h3>
            <form method="GET" class="row g-3">
                <div class="col-md-3">
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
                <div class="col-md-2">
                    <label class="form-label">Data Início</label>
                    <input type="date" name="data_inicio" class="form-control" value="<?php echo htmlspecialchars($dataInicio ?? ''); ?>">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Data Fim</label>
                    <input type="date" name="data_fim" class="form-control" value="<?php echo htmlspecialchars($dataFim ?? ''); ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        <option value="em_aberto" <?php echo $statusFiltro === 'em_aberto' ? 'selected' : ''; ?>>Em Aberto</option>
                        <option value="green" <?php echo $statusFiltro === 'green' ? 'selected' : ''; ?>>Green (Ganhou)</option>
                        <option value="red" <?php echo $statusFiltro === 'red' ? 'selected' : ''; ?>>Red (Perdeu)</option>
                    </select>
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
                                <th>Status</th>
                                <th>Usuário</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bets as $bet): ?>
                            <tr class="<?php 
                                if ($bet['status'] === 'green') echo 'table-green';
                                elseif ($bet['status'] === 'red') echo 'table-red';
                                elseif ($bet['status'] === 'em_aberto') echo 'table-warning';
                            ?>">
                                <td><?php echo htmlspecialchars($bet['data'] ?? '-'); ?></td>
                                <td>R$ <?php echo number_format(floatval($bet['valor_apostado'] ?? 0), 2, ',', '.'); ?></td>
                                <td><?php echo number_format(floatval($bet['odd'] ?? 0), 2, ',', '.'); ?></td>
                                <td>
                                    <?php echo $bet['green'] ? 'R$ ' . number_format(floatval($bet['green']), 2, ',', '.') : '-'; ?>
                                </td>
                                <td>
                                    <?php echo $bet['red'] !== null ? 'R$ ' . number_format(floatval($bet['red']), 2, ',', '.') : '-'; ?>
                                </td>
                                <td>
                                    <?php if ($bet['status'] === 'em_aberto'): ?>
                                        <span class="badge bg-warning text-dark">
                                            <i class="bi bi-clock"></i> Em Aberto
                                        </span>
                                    <?php elseif ($bet['status'] === 'green'): ?>
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> Green
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger">
                                            <i class="bi bi-x-circle"></i> Red
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($bet['usuario'] ?? '-'); ?></td>
                                <td>
                                    <?php if ($bet['status'] === 'em_aberto' && $bet['usuario'] === $usuarioLogado): ?>
                                        <button class="btn btn-primary btn-sm" onclick="fecharAposta(<?php echo $bet['id']; ?>, <?php echo $bet['valor_apostado']; ?>)">
                                            <i class="bi bi-check-square"></i> Fechar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <?php if ($isAdmin): ?>
                                        <button class="btn btn-danger btn-sm" onclick="confirmarExclusao(<?php echo $bet['id']; ?>)">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
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

    <!-- Modal de Confirmação dos Dados -->
    <?php if ($ocrData): ?>
    <div class="modal fade show" id="confirmModal" tabindex="-1" style="display: block; background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-check-circle"></i> Confirmar Dados da Aposta
                        <?php if ($uploadedImage): ?>
                            <span class="badge bg-info">Via OCR</span>
                        <?php else: ?>
                            <span class="badge bg-success">Cadastro Manual</span>
                        <?php endif; ?>
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <?php if ($uploadedImage): ?>
                        <!-- Mostra imagem SE foi upload -->
                        <div class="col-md-5">
                            <h6 class="mb-3">Preview da Imagem:</h6>
                            <img src="uploads/<?php echo htmlspecialchars($uploadedImage); ?>" class="preview-image" alt="Preview">
                        </div>
                        <div class="col-md-7">
                        <?php else: ?>
                        <!-- Sem imagem no modo manual -->
                        <div class="col-md-12">
                        <?php endif; ?>
                            <h6 class="mb-3">Dados da Aposta:</h6>
                            <form method="POST" id="confirmForm">
                                <input type="hidden" name="action" value="save_bet">
                                <input type="hidden" name="imagem_nome" value="<?php echo htmlspecialchars($uploadedImage ?? ''); ?>">
                                
                                <div class="mb-3">
                                    <label class="form-label">Usuário (não editável)</label>
                                    <input type="text" class="form-control" value="<?php echo htmlspecialchars($usuarioLogado); ?>" readonly>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Data da Aposta *</label>
                                    <input type="date" name="data" class="form-control" value="<?php echo $ocrData['data'] ?? date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Valor Apostado (R$) *</label>
                                        <input type="number" name="valor_apostado" id="valor_apostado" class="form-control" step="0.01" value="<?php echo $ocrData['valor_apostado'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">ODD *</label>
                                        <input type="number" name="odd" class="form-control" step="0.01" value="<?php echo $ocrData['odd'] ?? ''; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Retorno (R$)</label>
                                        <input type="number" name="retorno" id="retorno" class="form-control" step="0.01" value="<?php echo $ocrData['retorno'] ?? ''; ?>">
                                        <small class="text-muted">Deixe vazio se ainda não fechou</small>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="sem_resultado_modal" name="sem_resultado">
                                        <label class="form-check-label" for="sem_resultado_modal">
                                            <strong>Aposta ainda está em aberto</strong>
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Campos hidden para Green e Red -->
                                <input type="hidden" name="green" id="green" value="<?php echo $ocrData['green'] ?? ''; ?>">
                                <input type="hidden" name="red" id="red" value="<?php echo $ocrData['red'] ?? ''; ?>">
                                
                                <!-- Indicador visual de resultado -->
                                <div class="alert alert-info" id="resultado-preview">
                                    <div id="resultado-texto">
                                        <i class="bi bi-calculator"></i> Preencha os valores para ver o resultado
                                    </div>
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="bi bi-exclamation-triangle"></i> 
                                    <strong>Atenção:</strong> Verifique se os dados estão corretos antes de salvar!
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a href="index.php" class="btn btn-secondary">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                    <button type="submit" form="confirmForm" class="btn btn-success btn-lg">
                        <i class="bi bi-check-circle"></i> Confirmar e Salvar Aposta
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para Fechar Aposta -->
    <div class="modal fade" id="fecharApostaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fechar Aposta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="fecharApostaForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="fechar_aposta">
                        <input type="hidden" name="id" id="fechar_aposta_id">
                        
                        <p>Informe o <strong>valor do retorno</strong> para fechar esta aposta:</p>
                        
                        <div class="mb-3">
                            <label class="form-label">Valor Apostado:</label>
                            <input type="text" class="form-control" id="fechar_valor_apostado" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Retorno (R$) *</label>
                            <input type="number" name="retorno" id="fechar_retorno" class="form-control" step="0.01" required>
                            <small class="text-muted">Digite o valor que você recebeu de volta</small>
                        </div>
                        
                        <div class="alert alert-info" id="fechar-resultado-preview">
                            <div id="fechar-resultado-texto">
                                <i class="bi bi-calculator"></i> Digite o retorno para ver o resultado
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle"></i> Fechar Aposta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal de Exclusão (só admin) -->
    <?php if ($isAdmin): ?>
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
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Função para alternar entre formulários
        function mostrarFormulario(tipo) {
            const formManual = document.getElementById('form_manual');
            const formImagem = document.getElementById('form_imagem');
            
            if (tipo === 'manual') {
                formManual.style.display = 'block';
                formImagem.style.display = 'none';
            } else {
                formManual.style.display = 'none';
                formImagem.style.display = 'block';
            }
        }

        // Cálculo em tempo real no formulário manual
        document.addEventListener('DOMContentLoaded', function() {
            const manualValor = document.getElementById('manual_valor');
            const manualRetorno = document.getElementById('manual_retorno');
            const manualResultadoPreview = document.getElementById('manual-resultado-preview');
            const manualResultadoTexto = document.getElementById('manual-resultado-texto');
            const semResultadoCheck = document.getElementById('sem_resultado');
            
            function calcularResultadoManual() {
                const apostado = parseFloat(manualValor?.value) || 0;
                const retornoVal = parseFloat(manualRetorno?.value) || 0;
                
                if (semResultadoCheck?.checked) {
                    manualResultadoPreview.className = 'alert alert-warning';
                    manualResultadoTexto.innerHTML = '<i class="bi bi-clock"></i> <strong>Aposta será cadastrada como EM ABERTO</strong>';
                    return;
                }
                
                if (apostado > 0 && retornoVal > 0) {
                    if (retornoVal > apostado) {
                        // GREEN
                        manualResultadoPreview.className = 'alert alert-success';
                        const lucro = retornoVal - apostado;
                        manualResultadoTexto.innerHTML = `
                            <i class="bi bi-check-circle-fill"></i> 
                            <strong>GREEN!</strong> Você ganhou R$ ${lucro.toFixed(2)} 
                            (Retorno: R$ ${retornoVal.toFixed(2)} > Apostado: R$ ${apostado.toFixed(2)})
                        `;
                    } else {
                        // RED
                        manualResultadoPreview.className = 'alert alert-danger';
                        const prejuizo = apostado - retornoVal;
                        manualResultadoTexto.innerHTML = `
                            <i class="bi bi-x-circle-fill"></i> 
                            <strong>RED!</strong> Você perdeu R$ ${prejuizo.toFixed(2)} 
                            (Retorno: R$ ${retornoVal.toFixed(2)} <= Apostado: R$ ${apostado.toFixed(2)})
                        `;
                    }
                } else {
                    manualResultadoPreview.className = 'alert alert-info';
                    manualResultadoTexto.innerHTML = '<i class="bi bi-calculator"></i> Preencha os valores para ver o resultado';
                }
            }
            
            if (manualValor && manualRetorno) {
                manualValor.addEventListener('input', calcularResultadoManual);
                manualRetorno.addEventListener('input', calcularResultadoManual);
                semResultadoCheck?.addEventListener('change', calcularResultadoManual);
            }
            
            // Calcula automaticamente Green/Red no modal de confirmação
            const valorApostado = document.getElementById('valor_apostado');
            const retorno = document.getElementById('retorno');
            const greenField = document.getElementById('green');
            const redField = document.getElementById('red');
            const resultadoPreview = document.getElementById('resultado-preview');
            const resultadoTexto = document.getElementById('resultado-texto');
            const semResultadoModal = document.getElementById('sem_resultado_modal');
            
            function calcularResultado() {
                if (!valorApostado || !retorno) return;
                
                const apostado = parseFloat(valorApostado.value) || 0;
                const retornoVal = parseFloat(retorno.value) || 0;
                
                if (semResultadoModal?.checked) {
                    resultadoPreview.className = 'alert alert-warning';
                    resultadoTexto.innerHTML = '<i class="bi bi-clock"></i> <strong>Aposta será cadastrada como EM ABERTO</strong>';
                    greenField.value = '';
                    redField.value = '';
                    return;
                }
                
                if (apostado > 0 && retornoVal > 0) {
                    if (retornoVal > apostado) {
                        // GREEN
                        greenField.value = retornoVal;
                        redField.value = '';
                        resultadoPreview.className = 'alert alert-success';
                        const lucro = retornoVal - apostado;
                        resultadoTexto.innerHTML = `
                            <i class="bi bi-check-circle-fill"></i> 
                            <strong>GREEN!</strong> Você ganhou R$ ${lucro.toFixed(2)} 
                            (Retorno: R$ ${retornoVal.toFixed(2)} > Apostado: R$ ${apostado.toFixed(2)})
                        `;
                    } else {
                        // RED
                        greenField.value = '';
                        redField.value = '0';
                        resultadoPreview.className = 'alert alert-danger';
                        const prejuizo = apostado - retornoVal;
                        resultadoTexto.innerHTML = `
                            <i class="bi bi-x-circle-fill"></i> 
                            <strong>RED!</strong> Você perdeu R$ ${prejuizo.toFixed(2)} 
                            (Retorno: R$ ${retornoVal.toFixed(2)} <= Apostado: R$ ${apostado.toFixed(2)})
                        `;
                    }
                } else {
                    resultadoPreview.className = 'alert alert-info';
                    resultadoTexto.innerHTML = '<i class="bi bi-calculator"></i> Preencha os valores para ver o resultado';
                }
            }
            
            if (valorApostado && retorno) {
                valorApostado.addEventListener('input', calcularResultado);
                retorno.addEventListener('input', calcularResultado);
                semResultadoModal?.addEventListener('change', calcularResultado);
                calcularResultado(); // Calcula imediatamente se já tiver valores
            }
        });
        
        function fecharAposta(id, valorApostado) {
            document.getElementById('fechar_aposta_id').value = id;
            document.getElementById('fechar_valor_apostado').value = 'R$ ' + valorApostado.toFixed(2).replace('.', ',');
            
            const modal = new bootstrap.Modal(document.getElementById('fecharApostaModal'));
            modal.show();
            
            // Cálculo em tempo real
            const retornoInput = document.getElementById('fechar_retorno');
            const resultadoPreview = document.getElementById('fechar-resultado-preview');
            const resultadoTexto = document.getElementById('fechar-resultado-texto');
            
            retornoInput.value = '';
            
            retornoInput.addEventListener('input', function() {
                const retorno = parseFloat(retornoInput.value) || 0;
                
                if (retorno > 0) {
                    if (retorno > valorApostado) {
                        resultadoPreview.className = 'alert alert-success';
                        const lucro = retorno - valorApostado;
                        resultadoTexto.innerHTML = `
                            <i class="bi bi-check-circle-fill"></i> 
                            <strong>GREEN!</strong> Você ganhou R$ ${lucro.toFixed(2)}
                        `;
                    } else {
                        resultadoPreview.className = 'alert alert-danger';
                        const prejuizo = valorApostado - retorno;
                        resultadoTexto.innerHTML = `
                            <i class="bi bi-x-circle-fill"></i> 
                            <strong>RED!</strong> Você perdeu R$ ${prejuizo.toFixed(2)}
                        `;
                    }
                } else {
                    resultadoPreview.className = 'alert alert-info';
                    resultadoTexto.innerHTML = '<i class="bi bi-calculator"></i> Digite o retorno para ver o resultado';
                }
            });
        }
        
        function confirmarExclusao(id) {
            document.getElementById('deleteId').value = id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>