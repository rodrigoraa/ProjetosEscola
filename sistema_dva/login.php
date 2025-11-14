<?php
session_start();
$mensagem_erro = '';

// Se o usuário JÁ está logado, redireciona para o painel
if (isset($_SESSION['usuario_id'])) {
    header('Location: painel.php'); // Correto (painel.php está na raiz)
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once 'app/conexao.php'; // Correto (app/ está na raiz)
    
    $email = $_POST['email'];
    $senha_form = $_POST['senha'];

    if (empty($email) || empty($senha_form)) {
        $mensagem_erro = "Por favor, preencha o email e a senha.";
    } else {
        try {
            $sql = "SELECT * FROM usuarios WHERE email = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha_form, $usuario['senha'])) {
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nome'] = $usuario['nome'];
                $_SESSION['usuario_tipo'] = $usuario['tipo'];
                
                header('Location: painel.php'); // Correto
                exit();
            } else {
                $mensagem_erro = "Email ou senha inválidos.";
            }
        } catch (PDOException $e) {
            $mensagem_erro = "Erro no banco de dados. Tente novamente.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistema de DVAs</title>
    <link rel="stylesheet" href="assets/css/style.css"> </head>
<body class="login-container">
    <form action="login.php" method="POST" class="login"> <h2>Acesso ao Sistema</h2>
        <?php if (!empty($mensagem_erro)): ?>
            <p class="error-message"><?php echo $mensagem_erro; ?></p>
        <?php endif; ?>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div>
            <label for="senha">Senha:</label>
            <input type="password" id="senha" name="senha" required>
        </div>
        <div>
            <button type="submit">Entrar</button>
        </div>
    </form>
</body>
</html>