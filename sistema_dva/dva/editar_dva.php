<?php
// ATUALIZADO: Agora qualquer funcionário logado pode editar
require_once '../app/protecao_login.php'; 
require_once '../app/conexao.php';

// Função para remover acentos (para a coluna de ordenação)
function criar_nome_sort($str) {
    $str = str_replace(
        ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
        strtolower($str)
    );
    return strtoupper($str);
}

$mensagem = '';
$dva_data = null;
$id_dva_para_editar = $_GET['id'] ?? null;

// --- (Processamento POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_dva = $_POST['id_dva'];
    $id_aluno = $_POST['id_aluno'];
    $data_vencimento = $_POST['data_vencimento'];
    $observacao = trim($_POST['observacao']);
    try {
        $sql = "UPDATE dvas SET id_aluno = ?, data_vencimento = ?, observacao = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_aluno, $data_vencimento, $observacao, $id_dva]);
        header("Location: ../painel.php"); // Redireciona para o painel
        exit();
    } catch (PDOException $e) { $mensagem = '<p class="error-message">Erro: ' . $e->getMessage() . '</p>'; }
}

// --- (Processamento GET) ---
if ($id_dva_para_editar && !$mensagem) {
    try {
        $sql_dva = "SELECT * FROM dvas WHERE id = ?";
        $stmt_dva = $pdo->prepare($sql_dva);
        $stmt_dva->execute([$id_dva_para_editar]);
        $dva_data = $stmt_dva->fetch();
        if (!$dva_data) { die("Registro de DVA não encontrado."); }
        
        $query_alunos = $pdo->query("SELECT a.id, a.nome_completo, t.nome_turma FROM alunos a LEFT JOIN turmas t ON a.id_turma = t.id ORDER BY a.nome_completo");
        $lista_alunos = $query_alunos->fetchAll();
    } catch (PDOException $e) { die("Erro ao carregar dados: " . $e->getMessage()); }
} elseif (!$id_dva_para_editar) {
    header('Location: ../painel.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar DVA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Editar Registro de DVA</h1></header>
    <nav>
        <a href="../painel.php">Voltar ao Painel</a>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <p>Corrigindo o registro ID: <?php echo htmlspecialchars($dva_data['id']); ?></p>
        <?php echo $mensagem; ?>
        <?php 
        // Lógica para pré-preencher o formulário (mesmo se o POST falhar)
        $id_aluno_form = $_POST['id_aluno'] ?? $dva_data['id_aluno'] ?? '';
        $data_venc_form = $_POST['data_vencimento'] ?? $dva_data['data_vencimento'] ?? '';
        $obs_form = $_POST['observacao'] ?? $dva_data['observacao'] ?? '';
        $id_form = $_POST['id_dva'] ?? $dva_data['id'] ?? '';
        ?>
        
        <form action="editar_dva.php?id=<?php echo $id_dva_para_editar; ?>" method="POST" class="sistema">
            <input type="hidden" name="id_dva" value="<?php echo $id_form; ?>">
            <div>
                <label for="aluno">Aluno:</label>
                <select id="aluno" name="id_aluno" required>
                    <option value="">Selecione um aluno...</option>
                    <?php foreach ($lista_alunos as $aluno): ?>
                        <option value="<?php echo $aluno['id']; ?>" <?php echo ($aluno['id'] == $id_aluno_form) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($aluno['nome_completo']); ?> 
                            (<?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="data_vencimento">Nova Data de Vencimento:</label>
                <input type="date" id="data_vencimento" name="data_vencimento" value="<?php echo htmlspecialchars($data_venc_form); ?>" required>
            </div>
            <div>
                <label for="observacao">Observações:</label>
                <textarea id="observacao" name="observacao" rows="3"><?php echo htmlspecialchars($obs_form); ?></textarea>
            </div>
            <div>
                <button type="submit">Salvar Alterações</button>
                <a href="../painel.php" class="cancelar">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>