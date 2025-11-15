<?php
// (Arquivo NOVO: usuario/cadastrar_usuario.php)
require_once '../app/protecao_admin.php';
require_once '../app/conexao.php';
$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo'];

    if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
        $mensagem = '<p class="error-message">Todos os campos são obrigatórios!</p>';
    } elseif (strlen($senha) < 6) {
        $mensagem = '<p class="error-message">A senha deve ter pelo menos 6 caracteres.</p>';
    } else {
        try {
            $hash_senha = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $hash_senha, $tipo]);
            $mensagem = '<p class="success-message">Usuário cadastrado com sucesso!</p>';
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Email duplicado
                $mensagem = '<p class="error-message">Erro: Este e-mail já está cadastrado.</p>';
            } else {
                $mensagem = '<p class="error-message">Erro ao cadastrar: ' . $e->getMessage() . '</p>';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Usuário</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Cadastrar Novo Usuário</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="gerenciar_usuarios.php" style="color: #d9534f;">Gerenciar Usuários</a>
            <a href="../admin/backup.php" style="color: #d9534f; font-weight: bold;">Backup</a>
        <?php endif; ?>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <form action="cadastrar_usuario.php" method="POST" class="sistema">
            <?php echo $mensagem; ?>
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome" required>
            </div>
            <div>
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div>
                <label for="senha">Senha (mínimo 6 caracteres):</label>
                <div style="position: relative;">
                    <input type="password" id="senha" name="senha" required style="padding-right: 45px;">
                    <span id="togglePassword" style="position: absolute; right: 12px; top: 13px; cursor: pointer; user-select: none;">👁️</span>
                </div>
            </div>
            
            <div>
                <label for="tipo">Tipo de Conta:</label>
                <select id="tipo" name="tipo" required>
                    <option value="funcionario">Funcionário</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div>
                <button type="submit">Cadastrar Usuário</button>
                <a href="gerenciar_usuarios.php" class="cancelar">Cancelar</a>
            </div>
        </form>
    </main>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('senha');
        togglePassword.addEventListener('click', function () {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>