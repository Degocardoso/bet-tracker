<?php
session_start();

// Verifica se está logado e é admin
if (!isset($_SESSION['user_id']) || $_SESSION['username'] !== 'admin') {
    header('Location: index.php');
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

use App\UserManager;

$userManager = new UserManager();
$message = '';
$messageType = '';

// Processa ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $nomeCompleto = trim($_POST['nome_completo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        
        if (empty($username) || empty($password) || empty($nomeCompleto)) {
            $message = 'Preencha todos os campos obrigatórios.';
            $messageType = 'danger';
        } else {
            if ($userManager->createUser($username, $password, $nomeCompleto, $email)) {
                $message = 'Usuário criado com sucesso!';
                $messageType = 'success';
            } else {
                $message = 'Erro ao criar usuário. Username pode já existir.';
                $messageType = 'danger';
            }
        }
    }
    
    if ($action === 'toggle') {
        $id = intval($_POST['id']);
        if ($userManager->toggleUserStatus($id)) {
            $message = 'Status do usuário alterado!';
            $messageType = 'success';
        }
    }
    
    if ($action === 'delete') {
        $id = intval($_POST['id']);
        if ($userManager->deleteUser($id)) {
            $message = 'Usuário excluído com sucesso!';
            $messageType = 'success';
        } else {
            $message = 'Não é possível excluir o administrador.';
            $messageType = 'danger';
        }
    }
}

$users = $userManager->getAllUsers();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administração - Bet Tracker</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="d-flex justify-content-between align-items-center text-white mb-4">
            <div>
                <h1><i class="bi bi-gear"></i> Administração de Usuários</h1>
            </div>
            <div>
                <a href="index.php" class="btn btn-light">
                    <i class="bi bi-arrow-left"></i> Voltar ao Sistema
                </a>
            </div>
        </div>

        <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <!-- Criar Usuário -->
        <div class="main-card p-4 mb-4">
            <h3 class="mb-4"><i class="bi bi-person-plus"></i> Criar Novo Usuário</h3>
            <form method="POST" class="row g-3">
                <input type="hidden" name="action" value="create">
                
                <div class="col-md-3">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Senha *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Nome Completo *</label>
                    <input type="text" name="nome_completo" class="form-control" required>
                </div>
                
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-plus-circle"></i> Criar
                    </button>
                </div>
            </form>
        </div>

        <!-- Lista de Usuários -->
        <div class="main-card p-4">
            <h3 class="mb-4"><i class="bi bi-people"></i> Usuários Cadastrados</h3>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Username</th>
                            <th>Nome Completo</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Criado em</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                <?php if ($user['username'] === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($user['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($user['email'] ?? '-'); ?></td>
                            <td>
                                <?php if ($user['ativo']): ?>
                                    <span class="badge bg-success">Ativo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if ($user['id'] != 1): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="action" value="toggle">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-warning" title="Ativar/Desativar">
                                            <i class="bi bi-power"></i>
                                        </button>
                                    </form>
                                    
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Tem certeza?')">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger" title="Excluir">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>