<?php
// Arquivo na raiz
require_once 'app/protecao_login.php';
require_once 'app/conexao.php';

// Ajusta a proteção para funcionar na raiz
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php'); // Correção para arquivos na raiz
    exit();
}

$hoje_str = date('Y-m-d');
try {
    // CONSULTA 1: VENCIDAS
    $query_vencidas = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento < ? ORDER BY d.data_vencimento ASC");
    $query_vencidas->execute([$hoje_str]);
    $lista_vencidas = $query_vencidas->fetchAll();
    
    // CONSULTA 2: A VENCER
    $query_avencer = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento >= ? AND d.data_vencimento <= date('now', '+30 days') ORDER BY d.data_vencimento ASC");
    $query_avencer->execute([$hoje_str]);
    $lista_avencer = $query_avencer->fetchAll();
    
    // CONSULTA 3: VIGENTES
    $query_vigentes = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento > date('now', '+30 days') ORDER BY a.nome_completo");
    $query_vigentes->execute();
    $lista_vigentes = $query_vigentes->fetchAll();

} catch (PDOException $e) {
    die("Erro ao consultar o banco de dados: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Controle - DVAs</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><h1>Painel de Controle de DVAs</h1></header>
    <nav>
        <a href="painel.php">Início (Painel)</a>
        <a href="aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="usuario/gerenciar_usuarios.php" style="color: #d9534f; font-weight: bold;">Gerenciar Usuários</a>
        <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <h2>Situação das Declarações de Vacina (DVAs)</h2>
        <p>Data de hoje: <?php echo date('d/m/Y'); ?></p>
        
        <div style="margin-bottom: 20px;">
            <label for="filtroPainel" style="font-weight: bold; font-size: 1.2em;">Pesquisar Aluno:</label>
            <input type="search" id="filtroPainel" placeholder="Digite o nome do aluno para filtrar todas as tabelas..." class="sistema">
        </div>

        <div class="relatorio">
            <h3>🔴 DVAs Vencidas (<?php echo count($lista_vencidas); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Vencimento</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_vencidas)): ?> 
                        <tr><td colspan="4">Nenhuma DVA vencida.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_vencidas as $dva): ?>
                        <tr class="status-vencida item-filtrado">
                            <td class="nome-aluno"><?php echo htmlspecialchars($dva['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            
                            <td class="col-acoes">
                                <a href="dva/editar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="editar" style="font-weight: bold;">Atualizar DVA</a>
                                
                                <?php if ($tipo_usuario_logado == 'admin'): // "Apagar" visível só para Admin ?>
                                    <a href="dva/apagar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="relatorio">
            <h3>🟡 DVAs a Vencer (<?php echo count($lista_avencer); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Vencimento</th>
                        <th>Ações</th> </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_avencer)): ?> 
                        <tr><td colspan="4">Nenhuma DVA a vencer.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_avencer as $dva): ?>
                        <tr class="status-avencer item-filtrado">
                            <td class="nome-aluno"><?php echo htmlspecialchars($dva['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            
                            <td class="col-acoes">
                                <a href="dva/editar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="editar" style="font-weight: bold;">Atualizar DVA</a>
                                
                                <?php if ($tipo_usuario_logado == 'admin'): // "Apagar" visível só para Admin ?>
                                    <a href="dva/apagar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="relatorio">
            <h3>🟢 DVAs Vigentes (<?php echo count($lista_vigentes); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Vencimento</th>
                        <?php if ($tipo_usuario_logado == 'admin'): ?>
                            <th>Ações</th> <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_vigentes)): ?> 
                        <tr><td colspan="<?php echo ($tipo_usuario_logado == 'admin') ? '4' : '3'; ?>">Nenhuma DVA vigente.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_vigentes as $dva): ?>
                        <tr class="item-filtrado">
                            <td class="nome-aluno"><?php echo htmlspecialchars($dva['nome_completo']); ?></td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td class="status-vigente"><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            
                            <?php if ($tipo_usuario_logado == 'admin'): // Botões de "Editar" e "Apagar" normais para o Admin ?>
                                <td class="col-acoes">
                                    <a href="dva/editar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="editar">Editar</a>
                                    <a href="dva/apagar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        document.getElementById('filtroPainel').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let linhas = document.querySelectorAll('.item-filtrado');
            linhas.forEach(function(linha) {
                let nomeAluno = linha.querySelector('.nome-aluno').textContent.toLowerCase();
                linha.style.display = nomeAluno.includes(filtro) ? '' : 'none';
            });
        });
    </script>
</body>
</html>