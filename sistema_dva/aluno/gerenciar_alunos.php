<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';
$hoje = date('Y-m-d');

try {
    // Esta consulta busca todos os alunos
    // Lembrete: A ordenação 'nome_completo ASC' não lida bem com acentos (ex: 'Ágatha').
    // Isso é algo que podemos corrigir depois, se quiser.
    $query_alunos = $pdo->query("
        SELECT a.id, a.nome_completo, a.data_nascimento, t.nome_turma
        FROM alunos a 
        LEFT JOIN turmas t ON a.id_turma = t.id 
        ORDER BY a.nome_completo ASC
    ");
    $lista_alunos = $query_alunos->fetchAll();
} catch (PDOException $e) { 
    $lista_alunos = []; 
    $mensagem = '<p class="error-message">Erro ao carregar alunos.</p>'; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Alunos</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Gerenciar Alunos</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
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
        <a href="cadastrar_aluno.php" class="sistema" style="display: inline-block; text-decoration: none; background-color: #007bff; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">+ Cadastrar Novo Aluno</a>
        
        <div style="margin-bottom: 20px;">
            <input type="search" id="filtroAluno" placeholder="Pesquisar por nome ou turma..." class="sistema">
        </div>

        <div class="relatorio">
            <h3>Alunos Ativos (<?php echo count($lista_alunos); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Nome Completo</th>
                        <th>Data de Nasc.</th>
                        <th>Turma</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabelaAluno">
                    <?php if (empty($lista_alunos)): ?>
                        <tr><td colspan="4">Nenhum aluno cadastrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lista_alunos as $aluno): ?>
                    <tr class="item-filtrado">
                        
                        <td class="nome-aluno">
                            <a href="perfil_aluno.php?id=<?php echo $aluno['id']; ?>" style="text-decoration: none; color: #004a91; font-weight: 600;">
                                <?php echo htmlspecialchars($aluno['nome_completo']); ?>
                            </a>
                        </td>
                        
                        <td><?php echo (new DateTime($aluno['data_nascimento']))->format('d/m/Y'); ?></td>
                        <td class="turma-aluno"><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></td>
                        <td class="col-acoes">
                            <a href="editar_aluno.php?id=<?php echo $aluno['id']; ?>" class="editar">Editar</a>
                            <?php if ($tipo_usuario_logado == 'admin'): ?>
                                <a href="apagar_aluno.php?id=<?php echo $aluno['id']; ?>" class="apagar" onclick="return confirm('Tem certeza? Isso também apagará a DVA associada.');">Apagar</a>
                            <?php endif; ?>
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
                // Seleciona os elementos corretos para filtrar
                let nomeEl = linha.querySelector('.nome-aluno');
                let turmaEl = linha.querySelector('.turma-aluno');
                
                // Verifica se os elementos existem antes de ler
                let nome = nomeEl ? nomeEl.textContent.toLowerCase() : '';
                let turma = turmaEl ? turmaEl.textContent.toLowerCase() : '';
                
                if (nome.includes(filtro) || turma.includes(filtro)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>