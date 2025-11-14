<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';

// --- (Processamento POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_aluno = $_POST['id_aluno'];
    $data_vencimento = $_POST['data_vencimento'];
    $observacao = trim($_POST['observacao']);
    if (empty($id_aluno) || empty($data_vencimento)) {
        $mensagem = '<p class="error-message">Aluno e Data de Vencimento são obrigatórios!</p>';
    } else {
        try {
            // Query INSERT atualizada (sem data_emissao)
            $sql = "INSERT INTO dvas (id_aluno, id_usuario_registro, data_vencimento, observacao) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_aluno, $usuario_id_logado, $data_vencimento, $observacao]);
            $mensagem = '<p class="success-message">DVA registrada com sucesso!</p>';
        } catch (PDOException $e) {
            $mensagem = '<p class="error-message">Erro: ' . $e->getMessage() . '</p>';
        }
    }
}
// --- (Buscar Alunos) ---
try {
    $query_alunos = $pdo->query("SELECT a.id, a.nome_completo, t.nome_turma FROM alunos a LEFT JOIN turmas t ON a.id_turma = t.id ORDER BY a.nome_completo");
    $alunos = $query_alunos->fetchAll();
} catch (PDOException $e) {
    $alunos = [];
    $mensagem .= '<p class="error-message">Erro ao carregar alunos.</p>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>Cadastrar DVA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>

<body>
    <header>
        <h1>Cadastrar DVA</h1>
    </header>
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="cadastrar_dva.php">Cadastrar DVA</a> <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f; font-weight: bold;">Gerenciar Usuários</a>
        <?php endif; ?>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <?php echo $mensagem; ?>
        <form action="cadastrar_dva.php" method="POST" class="sistema">
            <div>
                <label for="filtroAluno">Pesquisar Aluno:</label>
                <input type="search" id="filtroAluno" placeholder="Digite o nome do aluno para filtrar a lista..." class="sistema">
            </div>
            <div>
                <label for="selectAluno">Aluno:</label>
                <select id="selectAluno" name="id_aluno" required size="8">
                    <option value="">Selecione um aluno...</option>
                    <?php foreach ($alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>">
                            <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                            (<?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="data_vencimento">Data de Vencimento:</label>
                <input type="date" id="data_vencimento" name="data_vencimento" required>
            </div>
            <div>
                <label for="observacao">Observações:</label>
                <textarea id="observacao" name="observacao" rows="3"></textarea>
            </div>
            <div><button type="submit">Registrar DVA</button></div>
        </form>
    </main>
    <script>
        document.getElementById('filtroAluno').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let select = document.getElementById('selectAluno');
            for (let i = 1; i < select.options.length; i++) { // Começa do 1 para ignorar o "Selecione..."
                let option = select.options[i];
                option.style.display = option.text.toLowerCase().includes(filtro) ? '' : 'none';
            }
        });
    </script>
</body>

</html>