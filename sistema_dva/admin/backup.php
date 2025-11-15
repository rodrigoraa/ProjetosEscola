<?php
// Proteção de ADMIN (só admin pode fazer backup)
require_once '../app/protecao_admin.php';
require_once '../app/conexao.php';

$mensagem = '';
$backup_dir = __DIR__ . '/../database/backups/';
$db_file    = __DIR__ . '/../database/escola.db';

// --- LÓGICA DE FAZER O BACKUP (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'criar_backup') {
    
    // 1. Tenta criar a pasta de backups se ela não existir
    if (!is_dir($backup_dir)) {
        if (!mkdir($backup_dir, 0755, true)) {
            $mensagem = '<p class="error-message">Erro: Não foi possível criar a pasta de backups.</p>';
        }
    }

    // 2. Define o nome do arquivo de backup com data e hora
    $backup_file = $backup_dir . 'escola_backup_' . date('Y-m-d_H-i-s') . '.db';

    // 3. Tenta copiar o banco de dados
    try {
        if (copy($db_file, $backup_file)) {
            $mensagem = '<p class="success-message">Backup criado com sucesso!</p>';
        } else {
            $mensagem = '<p class="error-message">Erro: Não foi possível copiar o arquivo do banco de dados.</p>';
        }
    } catch (Exception $e) {
        $mensagem = '<p class="error-message">Erro: ' . $e->getMessage() . '</p>';
    }
}

// --- LÓGICA DE LISTAR BACKUPS (GET) ---
$lista_de_backups = [];
if (is_dir($backup_dir)) {
    // Lista os arquivos, exceto '.' e '..'
    $files = array_diff(scandir($backup_dir), ['.', '..']);
    // Ordena do mais novo para o mais antigo
    rsort($files);
    $lista_de_backups = $files;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gestão de Backups</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Gestão de Backups</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/gerenciar_alunos.php">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php">Arquivo Passivo</a>
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php">Gerenciar Usuários</a>
            <a href="backup.php" style="font-weight: bold; color: #d9534f;">Backup</a>
        <?php endif; ?>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <div class="sistema">
            <h2>Criar Novo Backup</h2>
            <p>Clique no botão abaixo para criar uma cópia de segurança instantânea do banco de dados.</p>
            <p>O arquivo será salvo na pasta <strong>database/backups/</strong>.</p>
            
            <?php echo $mensagem; // Mostra o resultado (sucesso ou erro) aqui ?>
            
            <form action="backup.php" method="POST">
                <input type="hidden" name="action" value="criar_backup">
                <div>
                    <button type="submit" style="background-color: #007bff; min-width: 200px;">
                        Salvar Backup Agora
                    </button>
                </div>
            </form>
        </div>

        <div class="relatorio">
            <h3>Backups Salvos (<?php echo count($lista_de_backups); ?>)</h3>
            <p>Estes são os backups salvos, do mais novo para o mais antigo.</p>
            <table class="tabela-filtrada">
                <thead>
                    <tr><th>Nome do Arquivo</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_de_backups)): ?>
                        <tr><td>Nenhum backup encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lista_de_backups as $backup): ?>
                        <tr><td><?php echo htmlspecialchars($backup); ?></td></tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>