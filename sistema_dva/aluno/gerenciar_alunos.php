<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';
try {
    $query_alunos = $pdo->query("SELECT a.id, a.nome_completo, a.data_nascimento, t.nome_turma FROM alunos a LEFT JOIN turmas t ON a.id_turma = t.id ORDER BY a.nome_completo");
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
    <header>
        <h1>Gerenciar Alunos</h1>
    </header>
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
        <div style="margin-bottom: 20px;">
            <input type="search" id="filtroAluno" placeholder="Digite o nome do aluno para filtrar..." class="sistema">
        </div>
        <div class="relatorio">
            <h3>Alunos Cadastrados (<?php echo count($lista_alunos); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Nome Completo</th>
                        <th>Data de Nascimento</th>
                        <th>Turma</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabelaAlunos">
                    <?php foreach ($lista_alunos as $aluno): ?>
                        <tr class="item-filtrado">
                            <td class="nome-aluno"><?php echo htmlspecialchars($aluno['nome_completo']); ?></td>
                            <td><?php echo (new DateTime($aluno['data_nascimento']))->format('d/m/Y'); ?></td>
                            <td><?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td class="col-acoes">
                                <a href="editar_aluno.php?id=<?php echo $aluno['id']; ?>" class="editar">Editar</a>
                                <?php if ($tipo_usuario_logado == 'admin'): ?>
                                    <a href="apagar_aluno.php?id=<?php echo $aluno['id']; ?>" class="apagar"
                                        onclick="return confirm('Tem certeza?\nAPAGAR um aluno também APAGARÁ todas as suas DVAs!');">
                                        Apagar
                                    </a>
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
            let linhas = document.querySelectorAll('#corpoTabelaAlunos tr.item-filtrado');
            linhas.forEach(function(linha) {
                let nomeAluno = linha.querySelector('.nome-aluno').textContent.toLowerCase();
                linha.style.display = nomeAluno.includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>

</html>