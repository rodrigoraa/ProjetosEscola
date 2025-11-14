<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';
$aluno_data = null;
$id_aluno_para_editar = $_GET['id'] ?? null;

// --- (Processamento POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_aluno = $_POST['id_aluno'];
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento'];
    $id_turma = $_POST['id_turma'];
    try {
        $sql = "UPDATE alunos SET nome_completo = ?, data_nascimento = ?, id_turma = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$nome_completo, $data_nascimento, $id_turma, $id_aluno]);
        $mensagem = '<p class="success-message">Aluno atualizado! Redirecionando...</p>';
        header("Refresh: 2; url=gerenciar_alunos.php"); // Correto (mesma pasta)
    } catch (PDOException $e) { $mensagem = '<p class="error-message">Erro: ' . $e->getMessage() . '</p>'; }
}

// --- (Processamento GET) ---
if ($id_aluno_para_editar && !$mensagem) { // Só busca se não for um POST com erro
    try {
        $sql_aluno = "SELECT * FROM alunos WHERE id = ?";
        $stmt_aluno = $pdo->prepare($sql_aluno);
        $stmt_aluno->execute([$id_aluno_para_editar]);
        $aluno_data = $stmt_aluno->fetch();
        if (!$aluno_data) { die("Aluno não encontrado."); }
        
        $query_turmas = $pdo->query("SELECT * FROM turmas ORDER BY nome_turma");
        $lista_turmas = $query_turmas->fetchAll();
    } catch (PDOException $e) { die("Erro ao carregar dados: " . $e->getMessage()); }
} elseif (!$id_aluno_para_editar) {
    header('Location: gerenciar_alunos.php'); // Correto (mesma pasta)
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css"> </head>
<body>
    <header><h1>Editar Aluno</h1></header>
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="gerenciar_alunos.php">Voltar para Gerenciar Alunos</a>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <?php echo $mensagem; ?>
        <?php if ($aluno_data): ?>
        <form action="editar_aluno.php?id=<?php echo $aluno_data['id']; ?>" method="POST" class="sistema">
            <input type="hidden" name="id_aluno" value="<?php echo $aluno_data['id']; ?>">
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome_completo" value="<?php echo htmlspecialchars($aluno_data['nome_completo']); ?>" required>
            </div>
            <div>
                <label for="data_nasc">Data de Nascimento:</label>
                <input type="date" id="data_nasc" name="data_nascimento" value="<?php echo htmlspecialchars($aluno_data['data_nascimento']); ?>" required>
            </div>
            <div>
                <label for="turma">Turma:</label>
                <select id="turma" name="id_turma" required>
                    <option value="">Selecione uma turma</option>
                    <?php foreach ($lista_turmas as $turma): ?>
                        <option value="<?php echo $turma['id']; ?>" <?php echo ($turma['id'] == $aluno_data['id_turma']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($turma['nome_turma']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <button type="submit">Salvar Alterações</button>
                <a href="gerenciar_alunos.php" class="cancelar">Cancelar</a>
            </div>
        </form>
        <?php endif; ?>
    </main>
</body>
</html>