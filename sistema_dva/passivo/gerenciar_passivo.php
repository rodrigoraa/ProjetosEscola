<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php'; 
$mensagem = '';
$registros = [];
$total_registros = 0;

try {
    $total_registros = $pdo->query("SELECT COUNT(id) FROM alunos_passivo")->fetchColumn();
    $query_passivo = $pdo->query("SELECT * FROM alunos_passivo ORDER BY CAST(caixa AS INTEGER) ASC, nome_sort ASC");
    $registros = $query_passivo->fetchAll();
} catch (PDOException $e) {
    $mensagem = '<p class="error-message">Erro ao consultar o arquivo passivo: ' . $e->getMessage() . '</p>';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Arquivo Passivo</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Arquivo Passivo</h1></header>
    
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
        <a href="cadastrar_passivo.php" class="sistema" style="display: inline-block; text-decoration: none; background-color: #007bff; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">+ Adicionar Registro</a>
        
        <div class="filtros-container" style="display: grid; grid-template-columns: 200px 1fr; gap: 20px; margin-bottom: 20px;">
            <div>
                <label for="filtroCaixa" style="font-weight: bold; font-size: 1.1em;">Pesquisar por Caixa:</label>
                <input type="search" id="filtroCaixa" placeholder="Nº da caixa..." class="sistema">
            </div>
            <div>
                <label for="filtroNome" style="font-weight: bold; font-size: 1.1em;">Pesquisar por Nome:</label>
                <input type="search" id="filtroNome" placeholder="Nome do aluno..." class="sistema">
            </div>
        </div>
        
        <div class="relatorio passivo-tabela">
            <h3>Registros no Arquivo Passivo (<?php echo $total_registros; ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th>Caixa</th>
                        <th>Nome Completo</th>
                        <th>Data de Nasc.</th>
                        <th>Número</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabelaPassivo">
                    <?php if (empty($registros)): ?>
                        <tr><td colspan="5">Nenhum registro encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($registros as $reg): ?>
                    <tr class="item-filtrado">
                        <td class="filtro-caixa"><?php echo htmlspecialchars($reg['caixa']); ?></td>
                        <td class="filtro-nome"><?php echo htmlspecialchars($reg['nome_completo']); ?></td>
                        <td><?php echo htmlspecialchars($reg['data_nascimento'] ?? 'N/A'); ?></td>
                        <td class="filtro-numero"><?php echo htmlspecialchars($reg['numero'] ?? 'N/A'); ?></td>
                        <td class="col-acoes">
                            <a href="editar_passivo.php?id=<?php echo $reg['id']; ?>" class="editar">Editar</a>
                            <?php if ($tipo_usuario_logado == 'admin'): ?>
                                <a href="apagar_passivo.php?id=<?php echo $reg['id']; ?>" class="apagar" onclick="return confirm('Tem certeza?');">Apagar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        function removerAcentos(texto) { 
            if (!texto) return ""; 
            return texto.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, ""); 
        }

        const filtroCaixaInput = document.getElementById('filtroCaixa');
        const filtroNomeInput = document.getElementById('filtroNome');
        const linhasTabela = document.querySelectorAll('#corpoTabelaPassivo tr.item-filtrado');

        function aplicarFiltros() {
            let filtroCaixa = removerAcentos(filtroCaixaInput.value);
            let filtroNome = removerAcentos(filtroNomeInput.value);

            linhasTabela.forEach(function(linha) {
                let nome = removerAcentos(linha.querySelector('.filtro-nome').textContent);
                let caixa = removerAcentos(linha.querySelector('.filtro-caixa').textContent);
                
                if (nome.includes(filtroNome) && caixa.includes(filtroCaixa)) {
                    linha.style.display = '';
                } else {
                    linha.style.display = 'none';
                }
            });
        }

        // =============================================
        // (A CORREÇÃO ESTÁ AQUI)
        // Adiciona múltiplos "escutadores" para cobrir todos os casos
        // (digitar, colar, apagar com o "X")
        // =============================================
        ['keyup', 'search', 'input'].forEach(function(evento) {
            filtroCaixaInput.addEventListener(evento, aplicarFiltros);
            filtroNomeInput.addEventListener(evento, aplicarFiltros);
        });
    </script>
</body>
</html>