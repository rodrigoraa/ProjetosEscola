<?php
require_once 'app/protecao_login.php';
require_once 'app/conexao.php';

$hoje_str = date('Y-m-d');
try {
    
    // --- (CONSULTAS DE ESTATÍSTICA) ---
    // 1. Total de Alunos Ativos
    $total_alunos_ativos = $pdo->query("SELECT COUNT(id) FROM alunos")->fetchColumn();
    
    // 2. Total de Alunos SEM DVA
    $total_sem_dva = $pdo->query("
        SELECT COUNT(id) 
        FROM alunos 
        WHERE id NOT IN (SELECT id_aluno FROM dvas)
    ")->fetchColumn();


    // --- (CONSULTAS DAS LISTAS - COM ID DO ALUNO) ---
    
    // 1. VENCIDAS (com a.id AS aluno_id)
    $query_vencidas = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento < ? ORDER BY d.data_vencimento ASC");
    $query_vencidas->execute([$hoje_str]);
    $lista_vencidas = $query_vencidas->fetchAll();
    
    // 2. A VENCER (com a.id AS aluno_id)
    $query_avencer = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento >= ? AND d.data_vencimento <= date('now', '+30 days') ORDER BY d.data_vencimento ASC");
    $query_avencer->execute([$hoje_str]);
    $lista_avencer = $query_avencer->fetchAll();
    
    // 3. VIGENTES (com a.id AS aluno_id)
    $query_vigentes = $pdo->prepare("SELECT a.nome_completo, t.nome_turma, d.data_vencimento, d.id AS dva_id, a.id AS aluno_id FROM dvas d JOIN alunos a ON d.id_aluno = a.id LEFT JOIN turmas t ON a.id_turma = t.id WHERE d.data_vencimento > date('now', '+30 days') ORDER BY a.nome_completo");
    $query_vigentes->execute();
    $lista_vigentes = $query_vigentes->fetchAll();

} catch (PDOException $e) {
    die("Erro ao consultar o banco de dados: " . $e->getMessage());
}

// Para os cards
$total_vencidas = count($lista_vencidas);
$total_avencer = count($lista_avencer);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Painel de Controle - DVAs</title>
    <link rel="stylesheet" href="assets/css/style.css">
    
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background-color: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 25px;
            text-align: center;
        }
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.1em;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .stat-card .stat-number {
            font-size: 3em;
            font-weight: 700;
        }
        /* Cores específicas */
        .stat-card.total { .stat-number { color: #004a91; } }
        .stat-card.pendente { .stat-number { color: #f57c00; } } /* Laranja */
        .stat-card.vencida { .stat-number { color: #d9534f; } } /* Vermelho */
        .stat-card.avencer { .stat-number { color: #a67c00; } } /* Amarelo/Ouro */
    </style>
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
            <a href="usuario/gerenciar_usuarios.php" style="color: #d9534f;">Gerenciar Usuários</a>
            <a href="admin/backup.php" style="color: #d9534f; font-weight: bold;">Backup</a>
        <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <div class="stats-container">
            <div class="stat-card total">
                <h3>Total de Alunos</h3>
                <span class="stat-number"><?php echo $total_alunos_ativos; ?></span>
            </div>
            <div class="stat-card pendente">
                <h3>Alunos Sem DVA</h3>
                <span class="stat-number"><?php echo $total_sem_dva; ?></span>
            </div>
            <div class="stat-card vencida">
                <h3>DVAs Vencidas</h3>
                <span class="stat-number"><?php echo $total_vencidas; ?></span>
            </div>
            <div class="stat-card avencer">
                <h3>A Vencer (30 dias)</h3>
                <span class="stat-number"><?php echo $total_avencer; ?></span>
            </div>
        </div>

        <p>Data de hoje: <?php echo date('d/m/Y'); ?></p>
        
        <div style="margin-bottom: 20px;">
            <label for="filtroPainel" style="font-weight: bold; font-size: 1.2em;">Pesquisar Aluno:</label>
            <input type="search" id="filtroPainel" placeholder="Digite o nome do aluno para filtrar todas as tabelas..." class="sistema">
        </div>

        <div class="relatorio">
            <h3>🔴 DVAs Vencidas (<?php echo $total_vencidas; ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_vencidas)): ?> 
                        <tr><td colspan="4">Nenhuma DVA vencida.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_vencidas as $dva): ?>
                        <tr class="status-vencida item-filtrado">
                            <td class="nome-aluno">
                                <a href="aluno/perfil_aluno.php?id=<?php echo $dva['aluno_id']; ?>" style="text-decoration: none; color: #a60000; font-weight: 600;">
                                    <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            <td class="col-acoes">
                                <a href="dva/editar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="editar" style="font-weight: bold;">Atualizar DVA</a>
                                <?php if ($tipo_usuario_logado == 'admin'): ?>
                                    <a href="dva/apagar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="relatorio">
            <h3>🟡 DVAs a Vencer (<?php echo $total_avencer; ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Aluno</th>
                        <th>Turma</th>
                        <th>Vencimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_avencer)): ?> 
                        <tr><td colspan="4">Nenhuma DVA a vencer.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_avencer as $dva): ?>
                        <tr class="status-avencer item-filtrado">
                            <td class="nome-aluno">
                                <a href="aluno/perfil_aluno.php?id=<?php echo $dva['aluno_id']; ?>" style="text-decoration: none; color: #a67c00; font-weight: 600;">
                                    <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            <td class="col-acoes">
                                <a href="dva/editar_dva.php?id=<?php echo $dva['dva_id']; ?>" class="editar" style="font-weight: bold;">Atualizar DVA</a>
                                <?php if ($tipo_usuario_logado == 'admin'): ?>
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
                            <th>Ações</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lista_vigentes)): ?> 
                        <tr><td colspan="<?php echo ($tipo_usuario_logado == 'admin') ? '4' : '3'; ?>">Nenhuma DVA vigente.</td></tr> 
                    <?php endif; ?>
                    <?php foreach ($lista_vigentes as $dva): ?>
                        <tr class="item-filtrado">
                            <td class="nome-aluno">
                                <a href="aluno/perfil_aluno.php?id=<?php echo $dva['aluno_id']; ?>" style="text-decoration: none; color: #004a91; font-weight: 600;">
                                    <?php echo htmlspecialchars($dva['nome_completo']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($dva['nome_turma'] ?? 'Sem turma'); ?></td>
                            <td class="status-vigente"><?php echo (new DateTime($dva['data_vencimento']))->format('d/m/Y'); ?></td>
                            <?php if ($tipo_usuario_logado == 'admin'): ?>
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