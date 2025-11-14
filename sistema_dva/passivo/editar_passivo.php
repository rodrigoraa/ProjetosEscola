<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';

// Função para remover acentos
function criar_nome_sort($str) {
    $str = str_replace(
        ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
        strtolower($str)
    );
    return strtoupper($str);
}

$mensagem = '';
$passivo_data = null;
$id_passivo_para_editar = $_GET['id'] ?? null;

// --- PARTE 1: PROCESSAR O 'UPDATE' (POST) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_passivo = $_POST['id_passivo'];
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento'];
    $numero = trim($_POST['numero']);
    $caixa = trim($_POST['caixa']);

    if (empty($nome_completo) || empty($caixa)) {
        $mensagem = '<p class="error-message">Nome Completo e Nº da Caixa são obrigatórios!</p>';
    } else {
        try {
            // Gera o nome de ordenação
            $nome_de_sort = criar_nome_sort($nome_completo);

            $sql = "UPDATE alunos_passivo SET 
                        nome_completo = ?, 
                        data_nascimento = ?, 
                        numero = ?, 
                        caixa = ?,
                        nome_sort = ?
                    WHERE id = ?";
            
            $stmt = $pdo->prepare($sql);
            $data_nasc_db = empty($data_nascimento) ? null : $data_nascimento;
            
            $stmt->execute([$nome_completo, $data_nasc_db, $numero, $caixa, $nome_de_sort, $id_passivo]);
            
            $mensagem = '<p class="success-message">Registro atualizado! Redirecionando...</p>';
            header("Refresh: 2; url=gerenciar_passivo.php");
        } catch (PDOException $e) {
            $mensagem = '<p class="error-message">Erro ao atualizar: ' . $e->getMessage() . '</p>';
        }
    }
}

// --- PARTE 2: BUSCAR DADOS (GET) ---
if ($id_passivo_para_editar) {
    try {
        $sql_passivo = "SELECT * FROM alunos_passivo WHERE id = ?";
        $stmt_passivo = $pdo->prepare($sql_passivo);
        $stmt_passivo->execute([$id_passivo_para_editar]);
        $passivo_data = $stmt_passivo->fetch();

        if (!$passivo_data) {
            die("Registro não encontrado.");
        }
    } catch (PDOException $e) {
        die("Erro ao carregar dados: " . $e->getMessage());
    }
} else if ($_SERVER["REQUEST_METHOD"] != "POST") { 
    header('Location: gerenciar_passivo.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro Passivo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Editar Registro do Arquivo Passivo</h1></header>
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="gerenciar_passivo.php">Voltar para Arquivo Passivo</a>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <?php echo $mensagem; ?>
        
        <?php 
        // Se os dados do POST falharam, usa os dados do POST para re-preencher
        // Senão, usa os dados do banco ($passivo_data)
        $nome_form = $_POST['nome_completo'] ?? $passivo_data['nome_completo'] ?? '';
        $data_nasc_form = $_POST['data_nascimento'] ?? $passivo_data['data_nascimento'] ?? '';
        $numero_form = $_POST['numero'] ?? $passivo_data['numero'] ?? '';
        $caixa_form = $_POST['caixa'] ?? $passivo_data['caixa'] ?? '';
        $id_form = $_POST['id_passivo'] ?? $passivo_data['id'] ?? '';
        ?>

        <form action="editar_passivo.php?id=<?php echo $id_passivo_para_editar; ?>" method="POST" class="sistema">
            
            <input type="hidden" name="id_passivo" value="<?php echo $id_form; ?>">

            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome_completo" 
                       value="<?php echo htmlspecialchars($nome_form); ?>" required>
            </div>
            <div>
                <label for="data_nasc">Data de Nascimento (Opcional):</label>
                <input type="date" id="data_nasc" name="data_nascimento"
                       value="<?php echo htmlspecialchars($data_nasc_form); ?>">
            </div>
            <div>
                <label for="numero">Número (Matrícula/Registro):</label>
                <input type="text" id="numero" name="numero"
                       value="<?php echo htmlspecialchars($numero_form); ?>">
            </div>
            <div>
                <label for="caixa">Nº da Caixa:</label>
                <input type="text" id="caixa" name="caixa" 
                       value="<?php echo htmlspecialchars($caixa_form); ?>" required>
            </div>
            <div>
                <button type="submit">Salvar Alterações</button>
                <a href="gerenciar_passivo.php" class="cancelar">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>