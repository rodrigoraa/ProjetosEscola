<?php
require_once '../app/protecao_admin.php';
require_once '../app/conexao.php';
$mensagem = '';
$usuario_data = null;
$id_usuario_para_editar = $_GET['id'] ?? null;

// --- (Processamento POST - Atualizar Usuário) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_usuario = $_POST['id_usuario'];
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $tipo = $_POST['tipo'];
    $nova_senha = $_POST['nova_senha'];
    try {
        if (!empty($nova_senha)) {
            $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
            $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo = ?, senha = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $tipo, $senha_hash, $id_usuario]);
        } else {
            $sql = "UPDATE usuarios SET nome = ?, email = ?, tipo = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $tipo, $id_usuario]);
        }
        $mensagem = '<p class="success-message">Usuário atualizado! Redirecionando...</p>';
        header("Refresh: 2; url=gerenciar_usuarios.php"); // Correto (mesma pasta)
    } catch (PDOException $e) { $mensagem = '<p class="error-message">Erro (email duplicado?): ' . $e->getMessage() . '</p>'; }
}

// --- (Processamento GET) ---
if ($id_usuario_para_editar && !$mensagem) {
    try {
        $sql_usr = "SELECT id, nome, email, tipo FROM usuarios WHERE id = ?";
        $stmt_usr = $pdo->prepare($sql_usr);
        $stmt_usr->execute([$id_usuario_para_editar]);
        $usuario_data = $stmt_usr->fetch();
        if (!$usuario_data) { die("Usuário não encontrado."); }
    } catch (PDOException $e) { die("Erro ao carregar usuário: " . $e->getMessage()); }
} elseif (!$id_usuario_para_editar) {
    header('Location: gerenciar_usuarios.php'); // Correto (mesma pasta)
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Usuário</title>
    <link rel="stylesheet" href="../assets/css/style.css"> </head>
<body>
    <header><h1>Editar Usuário</h1></header>
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f;">Gerenciar Usuários</a>
            <a href="../admin/backup.php" style="color: #d9534f; font-weight: bold;">Backup</a> <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <?php echo $mensagem; ?>
        <?php if ($usuario_data): ?>
        <form action="editar_usuario.php?id=<?php echo $usuario_data['id']; ?>" method="POST" class="sistema">
            <input type="hidden" name="id_usuario" value="<?php echo $usuario_data['id']; ?>">
            <div>
                <label for="nome">Nome:</label>
                <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario_data['nome']); ?>" required>
            </div>
            <div>
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario_data['email']); ?>" required>
            </div>
            <div>
                <label for="tipo">Tipo:</label>
                <select id="tipo" name="tipo" required <?php echo ($usuario_data['id'] == $usuario_id_logado) ? 'disabled' : ''; ?>>
                    <option value="funcionario" <?php echo ($usuario_data['tipo'] == 'funcionario') ? 'selected' : ''; ?>>Funcionário</option>
                    <option value="admin" <?php echo ($usuario_data['tipo'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
                <?php if ($usuario_data['id'] == $usuario_id_logado): ?>
                    <small>(Não pode alterar o tipo da sua própria conta)</small>
                    <input type="hidden" name="tipo" value="<?php echo htmlspecialchars($usuario_data['tipo']); ?>">
                <?php endif; ?>
            </div>
            <hr>
            <div>
                <label for="nova_senha">Nova Senha (Opcional):</label>
                <input type="password" id="nova_senha" name="nova_senha">
                <small>Deixe em branco para não alterar a senha.</small>
            </div>
            <div>
                <button type="submit">Salvar Alterações</button>
                <a href="gerenciar_usuarios.php" class="cancelar">Cancelar</a>
            </div>
        </form>
        <?php endif; ?>
    </main>
</body>
</html>