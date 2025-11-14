<?php
// ATUALIZADO: ../app/
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';

$mensagem = '';

// --- ATUALIZADO: Processamento POST com Verificação ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_completo = trim($_POST['nome_completo']);
    $data_nascimento = $_POST['data_nascimento'];
    $id_turma = $_POST['id_turma'];

    // 1. Validação básica (campos vazios)
    if (empty($nome_completo) || empty($data_nascimento) || empty($id_turma)) {
        $mensagem = '<p class="error-message">Todos os campos são obrigatórios!</p>';
    } else {
        
        // --- INÍCIO DA NOVA VERIFICAÇÃO ---
        try {
            // 2. Verifica se o aluno já existe
            $sql_check = "SELECT COUNT(*) FROM alunos WHERE nome_completo = ? AND data_nascimento = ?";
            $stmt_check = $pdo->prepare($sql_check);
            $stmt_check->execute([$nome_completo, $data_nascimento]);
            $count = $stmt_check->fetchColumn();

            if ($count > 0) {
                // 3. Se existir, mostra o erro
                $mensagem = '<p class="error-message">Erro: Já existe um aluno cadastrado com este nome e data de nascimento!</p>';
            } else {
                // 4. Se não existir, insere o novo aluno
                $sql = "INSERT INTO alunos (nome_completo, data_nascimento, id_turma) VALUES (?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$nome_completo, $data_nascimento, $id_turma]);
                $mensagem = '<p class="success-message">Aluno cadastrado com sucesso!</p>';
            }
        } catch (PDOException $e) { 
            $mensagem = '<p class="error-message">Erro ao interagir com o banco de dados: ' . $e->getMessage() . '</p>'; 
        }
        // --- FIM DA NOVA VERIFICAÇÃO ---
    }
}
// --- FIM DO PROCESSAMENTO POST ---


// --- (Buscar Turmas - Sem alterações) ---
try {
    $query_turmas = $pdo->query("SELECT * FROM turmas ORDER BY nome_turma");
    $turmas = $query_turmas->fetchAll();
} catch (PDOException $e) { 
    $turmas = [];
    // Adiciona ao $mensagem se já não houver uma mensagem de POST
    if(empty($mensagem)) {
        $mensagem = '<p class="error-message">Erro ao carregar turmas: ' . $e->getMessage() . '</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css"> </head>
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
            <div><button type="submit">Cadastrar Aluno</button></div>
        </form>
    </main>
</body>
</html>