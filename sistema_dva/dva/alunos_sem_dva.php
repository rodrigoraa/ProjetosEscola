<?php
// (Arquivo NOVO: dva/alunos_sem_dva.php)
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';

try {
    // Esta é a consulta principal:
    // Seleciona alunos (a) ONDE o ID deles NÃO ESTÁ (NOT IN) na tabela de dvas.
    $query_pendentes = $pdo->query("
        SELECT a.id, a.nome_completo, t.nome_turma
        FROM alunos a 
        LEFT JOIN turmas t ON a.id_turma = t.id 
        WHERE a.id NOT IN (SELECT id_aluno FROM dvas)
        ORDER BY a.nome_completo ASC
    ");
    $lista_pendentes = $query_pendentes->fetchAll();
    
} catch (PDOException $e) { 
    $lista_pendentes = []; 
    $mensagem = '<p class="error-message">Erro ao carregar alunos pendentes.</p>'; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Alunos Sem DVA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Alunos com DVA Pendente</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="cadastrar_dva.php">Cadastrar DVA</a>
        <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f;">Gerenciar Usuários</a>
            <a href="../admin/backup.php" style="color: #d9534f; font-weight: bold;">Backup</a>
        <?php endif; ?>
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <?php echo $mensagem; ?>
        
        <div style="margin-bottom: 20px;">
            <input type="search" id="filtroAluno" placeholder="Pesquisar por nome ou turma..." class="sistema">
        </div>

        <div class="relatorio">
            <h3>Alunos Sem Registro de DVA (<?php echo count($lista_pendentes); ?>)</h3>
            <p>Esta lista mostra todos os alunos ativos que ainda não possuem uma DVA cadastrada.</p>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Nome Completo (Link para Perfil)</th>
                        <th>Turma</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabelaAluno">
                    <?php if (empty($lista_pendentes)): ?>
                        <tr><td colspan="3">Nenhum aluno com DVA pendente.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lista_pendentes as $aluno): ?>
                    <tr class="item-filtrado">
                        <td class="nome-aluno">
                            <a href="../aluno/perfil_aluno.php?id=<?php echo $aluno['id']; ?>" style="text-decoration: none; color: #004a91; font-weight: 600;">
                                <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                            </a>
                        </td>
                        <td class="turma-aluno"><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></td>
                        <td class="col-acoes">
                            <a href="cadastrar_dva.php" class="editar" style="font-weight:bold; background-color: #007bff; color: white; padding: 5px 10px; border-radius: 4px;">
                                Registrar DVA
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <script>
        document.getElementById('filtroAluno').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let linhas = document.querySelectorAll('#corpoTabelaAluno tr');
            linhas.forEach(function(linha) {
                let nomeEl = linha.querySelector('.nome-aluno');
                let turmaEl = linha.querySelector('.turma-aluno');
                let nome = nomeEl ? nomeEl.textContent.toLowerCase() : '';
                let turma = turmaEl ? turmaEl.textContent.toLowerCase() : '';
                
                linha.style.display = (nome.includes(filtro) || turma.includes(filtro)) ? '' : 'none';
            });
        });
    </script>
</body>
</html>