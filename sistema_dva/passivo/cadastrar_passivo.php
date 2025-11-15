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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento']; // Pode ser vazio
    $numero = trim($_POST['numero']);
    $caixa = trim($_POST['caixa']);

    if (empty($nome_completo) || empty($caixa)) {
        $mensagem = '<p class="error-message">Nome Completo e Nº da Caixa são obrigatórios!</p>';
    } else {
        try {
            // Gera o nome de ordenação
            $nome_de_sort = criar_nome_sort($nome_completo);
            
            $sql = "INSERT INTO alunos_passivo (nome_completo, data_nascimento, numero, caixa, nome_sort) VALUES (?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            
            $data_nasc_db = empty($data_nascimento) ? null : $data_nascimento;
            
            $stmt->execute([$nome_completo, $data_nasc_db, $numero, $caixa, $nome_de_sort]);
            $mensagem = '<p class="success-message">Registro salvo com sucesso! Redirecionando...</p>';
            header("Refresh: 2; url=gerenciar_passivo.php");
        } catch (PDOException $e) {
            $mensagem = '<p class="error-message">Erro ao salvar: ' . $e->getMessage() . '</p>';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Registro Passivo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Cadastrar Novo Registro Passivo</h1></header>
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
        <form action="cadastrar_passivo.php" method="POST" class="sistema">
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome_completo" required>
            </div>
            <div>
                <label for="data_nasc">Data de Nascimento (Opcional):</label>
                <input type="date" id="data_nasc" name="data_nascimento">
            </div>
            <div>
                <label for="numero">Número (Matrícula/Registro):</label>
                <input type="text" id="numero" name="numero">
            </div>
            <div>
                <label for="caixa">Nº da Caixa:</label>
                <input type="text" id="caixa" name="caixa" required>
            </div>
            <div>
                <button type="submit">Salvar Registro</button>
                <a href="gerenciar_passivo.php" class="cancelar">Cancelar</a>
            </div>
        </form>
    </main>
</body>
</html>