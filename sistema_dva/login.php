<?php
require_once 'app/conexao.php';
session_start();
$mensagem_erro = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (empty($email) || empty($senha)) {
        $mensagem_erro = "E-mail e senha são obrigatórios.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];
                header("Location: painel.php");
                exit();
            } else {
                $mensagem_erro = "E-mail ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro no banco de dados: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema DVA</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-container">
    <form action="login.php" method="POST" class="login">
        <h2>Acesso ao Sistema</h2>
        <?php if (!empty($mensagem_erro)): ?>
            <p class="error-message"><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>
        <div>
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email" required autofocus>
        </div>

        <div>
            <label for="senha">Senha:</label>
            <div style="position: relative;">
                <input type="password" id="senha" name="senha" required style="padding-right: 45px;">
                <span id="togglePassword" style="position: absolute; right: 12px; top: 15px; cursor: pointer; user-select: none;">👁️</span>
            </div>
        </div>
        <div>
            <button type="submit">Entrar</button>
        </div>
    </form>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const password = document.getElementById('senha');

        togglePassword.addEventListener('click', function () {
            // Alterna o tipo do input
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Alterna o ícone
            this.textContent = type === 'password' ? '👁️' : '🙈';
        });
    </script>
</body>
</html>