<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';

$mensagem = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento'];
    $id_turma = $_POST['id_turma'];
    $data_vencimento_dva = $_POST['data_vencimento_dva'];
    $observacao_dva = trim($_POST['observacao_dva']);

    if (empty($nome_completo) || empty($data_nascimento) || empty($id_turma)) {
        $mensagem = '<p class="error-message">Nome, Data de Nascimento e Turma são obrigatórios!</p>';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Verifica se o aluno já existe
            $sql_check = "SELECT COUNT(*) FROM alunos WHERE nome_completo = ? AND data_nascimento = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$nome_completo, $data_nascimento]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                throw new Exception("Já existe um aluno cadastrado com este nome e data de nascimento!");
            }
            
            // 2. Insere o Aluno
            $sql_aluno = "INSERT INTO alunos (nome_completo, data_nascimento, id_turma) VALUES (?, ?, ?)";
            $stmt_aluno = $pdo->prepare($sql_aluno);
            $stmt_aluno->execute([$nome_completo, $data_nascimento, $id_turma]);
            $id_novo_aluno = $pdo->lastInsertId();

            // 3. Insere a DVA (se a data foi fornecida)
            if (!empty($data_vencimento_dva)) {
                
                // <<< MUDANÇA IMPORTANTE AQUI
                // Usa "INSERT OR REPLACE" para garantir que só exista uma DVA
                $sql_dva = "INSERT OR REPLACE INTO dvas 
                                (id_aluno, id_usuario_registro, data_vencimento, observacao) 
                            VALUES (?, ?, ?, ?)";
                // >>> FIM DA MUDANÇA
                
                $stmt_dva = $pdo->prepare($sql_dva);
                $stmt_dva->execute([$id_novo_aluno, $usuario_id_logado, $data_vencimento_dva, $observacao_dva]);
            }

            $pdo->commit();
            $mensagem = '<p class="success-message">Aluno cadastrado com sucesso!</p>';

        } catch (Exception $e) {
            $pdo->rollBack();
            $mensagem = '<p class="error-message">Erro ao cadastrar: ' . $e->getMessage() . '</p>'; 
        }
    }
}

// ... (O resto do arquivo, GET para buscar turmas e o HTML, continua O MESMO) ...
try {
    $query_turmas = $pdo->query("SELECT * FROM turmas ORDER BY nome_turma");
    $turmas = $query_turmas->fetchAll();
} catch (PDOException $e) { 
    $turmas = [];
    if(empty($mensagem)) { $mensagem = '<p class="error-message">Erro ao carregar turmas: ' . $e->getMessage() . '</p>'; }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Cadastro de Aluno</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f; font-weight: bold;">Gerenciar Usuários</a>
        <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <?php echo $mensagem; ?>
        
        <form action="cadastrar_aluno.php" method="POST" class="sistema">
            <h2>Dados do Aluno</h2>
            <div>
                <label for="nome">Nome Completo:</label>
                <input type="text" id="nome" name="nome_completo" required>
            </div>
            <div>
                <label for="data_nasc">Data de Nascimento:</label>
                <input type="date" id="data_nasc" name="data_nascimento" required>
            </div>
            <div>
                <label for="turma">Turma:</label>
                <select id="turma" name="id_turma" required>
                    <option value="">Selecione uma turma</option>
                    <?php foreach ($turmas as $turma): ?>
                        <option value="<?php echo $turma['id']; ?>">
                            <?php echo htmlspecialchars($turma['nome_turma']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 30px 0;">
            
            <h2>Dados da DVA (Opcional)</h2>
            <p>Preencha os campos abaixo para registrar a primeira DVA deste aluno.</p>
            
            <div>
                <label for="data_vencimento_dva">Data de Vencimento da DVA:</label>
                <input type="date" id="data_vencimento_dva" name="data_vencimento_dva">
            </div>
            <div>
                <label for="observacao_dva">Observações da DVA:</label>
                <textarea id="observacao_dva" name="observacao_dva" rows="3"></textarea>
            </div>

            <div>
                <button type="submit">Cadastrar Aluno (e DVA)</button>
            </div>
        </form>
    </main>
</body>
</html>