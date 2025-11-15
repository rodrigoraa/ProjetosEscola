<?php
require_once '../app/protecao_admin.php'; // Proteção de Admin
require_once '../app/conexao.php';
$mensagem = '';

// --- (Processamento POST - Criar Usuário) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];
    $tipo = $_POST['tipo'];
    if (empty($nome) || empty($email) || empty($senha) || empty($tipo)) {
        $mensagem = '<p class="error-message">Todos os campos são obrigatórios!</p>';
    } else {
        try {
            $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nome, $email, $senha_hash, $tipo]);
            $mensagem = '<p class="success-message">Usuário criado com sucesso!</p>';
        } catch (PDOException $e) {
            $mensagem = '<p class="error-message">Erro (email duplicado?): ' . $e->getMessage() . '</p>';
        }
    }
}
// --- (Buscar Lista de Usuários) ---
try {
    $query_usuarios = $pdo->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY nome");
    $lista_usuarios = $query_usuarios->fetchAll();
} catch (PDOException $e) {
    $lista_usuarios = [];
    $mensagem .= '<p class="error-message">Erro ao carregar usuários.</p>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Gerenciar Usuários</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header>
        <h1>Gerenciar Usuários</h1>
    </header>
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
        <form action="gerenciar_usuarios.php" method="POST" class="sistema" style="margin-bottom: 30px;">
            <h2>Adicionar Novo Usuário</h2>
            <div><label for="nome">Nome:</label><input type="text" id="nome" name="nome" required></div>
            <div><label for="email">Email:</label><input type="email" id="email" name="email" required></div>
            <div><label for="senha">Senha:</label><input type="password" id="senha" name="senha" required></div>
            <div><label for="tipo">Tipo:</label><select id="tipo" name="tipo" required>
                    <option value="funcionario">Funcionário</option>
                    <option value="admin">Administrador</option>
                </select></div>
            <div><button type="submit">Criar Usuário</button></div>
        </form>
        <div class="relatorio">
            <h3>Usuários Existentes</h3>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
                <?php foreach ($lista_usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['tipo']); ?></td>
                        <td class="col-acoes">
                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="editar">Editar</a>
                            <?php if ($usuario['id'] != $usuario_id_logado): ?>
                                <a href="apagar_usuario.php?id=<?php echo $usuario['id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                            <?php else: ?>
                                (Você)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </main>
</body>

</html>