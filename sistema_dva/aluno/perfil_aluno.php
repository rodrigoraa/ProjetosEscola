<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';

$aluno_id = $_GET['id'] ?? null;
if (!$aluno_id) {
    header('Location: gerenciar_alunos.php');
    exit();
}

try {
    // 1. Busca os dados do aluno e da turma
    $sql_aluno = "SELECT a.*, t.nome_turma 
                  FROM alunos a 
                  LEFT JOIN turmas t ON a.id_turma = t.id 
                  WHERE a.id = ?";
    $stmt_aluno = $pdo->prepare($sql_aluno);
    $stmt_aluno->execute([$aluno_id]);
    $aluno = $stmt_aluno->fetch();

    if (!$aluno) {
        die("Aluno não encontrado.");
    }

    // 2. Busca os dados da DVA
    $sql_dva = "SELECT d.*, u.nome AS nome_usuario 
                FROM dvas d
                LEFT JOIN usuarios u ON d.id_usuario_registro = u.id
                WHERE d.id_aluno = ?";
    $stmt_dva = $pdo->prepare($sql_dva);
    $stmt_dva->execute([$aluno_id]);
    $dva = $stmt_dva->fetch(); // $dva será 'false' se não houver DVA

} catch (PDOException $e) {
    die("Erro ao carregar dados: " . $e->getMessage());
}

// 3. Define o status da DVA para exibição
$dva_status_texto = 'Sem Registro';
$dva_status_classe = 'pendente';
if ($dva) {
    $hoje = date('Y-m-d');
    if ($dva['data_vencimento'] < $hoje) {
        $dva_status_texto = 'Vencida';
        $dva_status_classe = 'vencida';
    } else {
        $dva_status_texto = 'Vigente';
        $dva_status_classe = 'vigente';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Aluno</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .perfil-container { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
        .perfil-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); padding: 30px; }
        .perfil-card h2 { margin-top: 0; padding-bottom: 15px; border-bottom: 1px solid #eee; }
        .perfil-info { display: grid; grid-template-columns: 150px 1fr; gap: 15px; }
        .perfil-info strong { color: #555; }
        .status-dva { font-size: 1.2em; font-weight: bold; padding: 5px 10px; border-radius: 6px; color: #fff; }
        .status-dva.vigente { background-color: #006b0d; }
        .status-dva.vencida { background-color: #d9534f; }
        .status-dva.pendente { background-color: #f57c00; }
        .btn-acao { display: inline-block; text-decoration: none; padding: 10px 15px; border-radius: 8px; font-weight: 600; transition: all 0.2s ease; }
        .btn-editar-aluno { background-color: #f0f0f0; color: #333; }
        .btn-editar-aluno:hover { background-color: #e0e0e0; }
        .btn-atualizar-dva { background-color: #007bff; color: #fff; }
        .btn-atualizar-dva:hover { background-color: #0056b3; }
    </style>
</head>
<body>
    <header><h1>Perfil do Aluno</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f;">Gerenciar Usuários</a>
            <a href="../admin/backup.php" style="color: #d9534f; font-weight: bold;">Backup</a>
        <?php endif; ?>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <div class="perfil-container">
            <div class="perfil-card">
                <h2>Dados Pessoais</h2>
                <div class="perfil-info">
                    <strong>Nome:</strong>
                    <span><?php echo htmlspecialchars($aluno['nome_completo']); ?></span>
                    
                    <strong>Nascimento:</strong>
                    <span><?php echo (new DateTime($aluno['data_nascimento']))->format('d/m/Y'); ?></span>
                    
                    <strong>Turma:</strong>
                    <span><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Não matriculado'); ?></span>
                </div>
                
                <div style="text-align: right; margin-top: 20px;">
                    <a href="editar_aluno.php?id=<?php echo $aluno['id']; ?>" class="btn-acao btn-editar-aluno">Editar Dados</a>
                </div>
            </div>

            <div class="perfil-card">
                <h2>Situação da DVA</h2>
                <div class="perfil-info">
                    <strong>Status:</strong>
                    <span><span class="status-dva <?php echo $dva_status_classe; ?>"><?php echo $dva_status_texto; ?></span></span>
                    
                    <strong>Vencimento:</strong>
                    <span><?php echo $dva ? (new DateTime($dva['data_vencimento']))->format('d/m/Y') : 'N/A'; ?></span>
                    
                    <strong>Registrado por:</strong>
                    <span><?php echo htmlspecialchars($dva['nome_usuario'] ?? 'N/A'); ?></span>
                    
                    <strong>Observações:</strong>
                    <span><?php echo htmlspecialchars($dva['observacao'] ?? 'Nenhuma'); ?></span>
                </div>

                <div style="text-align: right; margin-top: 20px;">
                    <a href="../dva/cadastrar_dva.php" class="btn-acao btn-atualizar-dva">
                        <?php echo $dva ? 'Atualizar DVA' : 'Registrar DVA'; ?>
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>