<?php
// Proteção de LOGIN (Qualquer funcionário pode ver)
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';

try {
    // Consulta SQL com a ordenação correta por 'caixa' e 'nome_sort'
    $query_passivo = $pdo->query("
        SELECT id, nome_completo, data_nascimento, numero, caixa 
        FROM alunos_passivo 
        ORDER BY CAST(caixa AS INTEGER) ASC, nome_sort ASC
    ");
    $lista_passivo = $query_passivo->fetchAll();
} catch (PDOException $e) {
    $lista_passivo = [];
    $mensagem = '<p class="error-message">Erro ao carregar arquivo passivo.</p>';
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
    <header><h1>Arquivo Passivo de Alunos</h1></header>
    
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="../dva/cadastrar_dva.php">Cadastrar DVA</a>
        <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f; font-weight: bold;">Gerenciar Usuários</a>
        <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>

    <main>
        <?php echo $mensagem; ?>
        
        <a href="cadastrar_passivo.php" class="sistema" style="display: inline-block; text-decoration: none; background-color: #007bff; color: white; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
            + Cadastrar Novo Registro
        </a>

        <div style="display: flex; gap: 20px; margin-bottom: 20px;">
            <div style="flex: 3;">
                <label for="filtroNome" style="font-weight: bold;">Pesquisar por Nome:</label>
                <input type="search" id="filtroNome" 
                       placeholder="Digite o nome do aluno..."
                       class="sistema" style="margin-bottom: 0;">
            </div>
            <div style="flex: 1;">
                <label for="filtroCaixa" style="font-weight: bold;">Pesquisar por Caixa:</label>
                <input type="search" id="filtroCaixa" 
                       placeholder="Digite a caixa..."
                       class="sistema" style="margin-bottom: 0;">
            </div>
        </div>

        <div class="relatorio">
            <h3>Registros no Arquivo Passivo (<?php echo count($lista_passivo); ?>)</h3>
            <table class="tabela-filtrada">
                <thead>
                    <tr>
                        <th class="caixa">Caixa</th>
                        <th class="nome-aluno">Nome Completo</th>
                        <th>Data de Nasc.</th>
                        <th class="numero">Número</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody id="corpoTabelaPassivo">
                    <?php if (empty($lista_passivo)): ?>
                        <tr><td colspan="5">Nenhum registro encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($lista_passivo as $aluno): ?>
                    <tr class="item-filtrado">
                        <td class="caixa" style="font-weight: bold;"><?php echo htmlspecialchars($aluno['caixa']); ?></td>
                        <td class="nome-aluno"><?php echo htmlspecialchars($aluno['nome_completo']); ?></td>
                        <td><?php echo $aluno['data_nascimento'] ? (new DateTime($aluno['data_nascimento']))->format('d/m/Y') : 'N/A'; ?></td>
                        <td class="numero"><?php echo htmlspecialchars($aluno['numero']); ?></td>
                        <td class="col-acoes">
                            <a href="editar_passivo.php?id=<?php echo $aluno['id']; ?>" class="editar">Editar</a>
                            <a href="apagar_passivo.php?id=<?php echo $aluno['id']; ?>" class="apagar" 
                               onclick="return confirm('Tem certeza que quer apagar este registro do arquivo passivo?');">
                               Apagar
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <script>
        // Pega os elementos do DOM
        const inputNome = document.getElementById('filtroNome');
        const inputCaixa = document.getElementById('filtroCaixa');
        const linhas = document.querySelectorAll('#corpoTabelaPassivo tr.item-filtrado');

        // --- FUNÇÃO NOVA PARA REMOVER ACENTOS ---
        // Converte "César" para "cesar"
        function removerAcentos(texto) {
            if (!texto) return "";
            return texto.toLowerCase()
                        .normalize("NFD") // Separa a letra do acento
                        .replace(/[\u0300-\u036f]/g, ""); // Remove os acentos
        }
        // ----------------------------------------

        // Função que faz a filtragem
        function filtrarTabela() {
            // ATUALIZADO: Aplica a função de remover acentos
            let filtroNome = removerAcentos(inputNome.value);
            let filtroCaixa = inputCaixa.value.toLowerCase(); // Caixa não tem acentos

            linhas.forEach(function(linha) {
                // ATUALIZADO: Aplica a função de remover acentos
                let nome = removerAcentos(linha.querySelector('.nome-aluno').textContent);
                let caixa = linha.querySelector('.caixa').textContent.toLowerCase();

                // Compara
                let matchNome = nome.includes(filtroNome);
                let matchCaixa = caixa.includes(filtroCaixa);

                // A linha só aparece se BATER COM O FILTRO DE NOME
                // E TAMBÉM BATER COM O FILTRO DE CAIXA
                if (matchNome && matchCaixa) {
                    linha.style.display = ''; // Mostra a linha
                } else {
                    linha.style.display = 'none'; // Esconde a linha
                }
            });
        }

        // Adiciona o "escutador" para os dois campos
        inputNome.addEventListener('keyup', filtrarTabela);
        inputCaixa.addEventListener('keyup', filtrarTabela);
    </script>
</body>
</html>