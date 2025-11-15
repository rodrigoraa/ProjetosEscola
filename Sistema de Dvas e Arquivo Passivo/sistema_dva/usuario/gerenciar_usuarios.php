<?php
// (Arquivo ATUALIZADO: usuario/gerenciar_usuarios.php)
require_once '../app/protecao_admin.php';
require_once '../app/conexao.php';
$mensagem = '';

try {
    $query_usuarios = $pdo->query("SELECT id, nome, email, tipo FROM usuarios ORDER BY nome");
    $lista_usuarios = $query_usuarios->fetchAll();
} catch (PDOException $e) {
    $lista_usuarios = [];
    $mensagem = '<p class="error-message">Erro ao carregar usuários.</p>';
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
    <header><h1>Gerenciar Usuários</h1></header>
    
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
        <?php echo $mensagem; ?>
        
        <a href="cadastrar_usuario.php" class="sistema" style="display: inline-block; text-decoration: none; background-color: #007bff; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
            + Cadastrar Novo Usuário
        </a>

        <div class="relatorio">
            <h3>Usuários Cadastrados (<?php echo count($lista_usuarios); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Tipo</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_usuarios)): ?>
                        <tr><td colspan="4">Nenhum usuário encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lista_usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['nome']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars(ucfirst($usuario['tipo'])); // ucfirst deixa a 1ª letra maiúscula ?></td>
                        <td class="col-acoes">
                            <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="editar">Editar</a>
                            <a href="apagar_usuario.php?id=<?php echo $usuario['id']; ?>" class="apagar" 
                               onclick="return confirm('Tem certeza que quer apagar este usuário?');">
                               Apagar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>