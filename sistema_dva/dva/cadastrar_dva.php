<?php
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';
$mensagem = '';
$json_alunos_com_dva = '[]'; // Valor padrão

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_aluno = $_POST['id_aluno'];
    $data_vencimento = $_POST['data_vencimento'];
    $observacao = trim($_POST['observacao']);
    
    if (empty($id_aluno) || empty($data_vencimento)) {
        $mensagem = '<p class="error-message">Aluno e Data de Vencimento são obrigatórios!</p>';
    } else {
        try {
            // A lógica "INSERT OR REPLACE" continua correta.
            // O usuário terá confirmado a substituição no navegador.
            $sql = "INSERT OR REPLACE INTO dvas 
                        (id_aluno, id_usuario_registro, data_vencimento, observacao) 
                    VALUES (?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id_aluno, $usuario_id_logado, $data_vencimento, $observacao]);
            
            $mensagem = '<p class="success-message">DVA registrada/atualizada com sucesso!</p>';
            
        } catch (PDOException $e) { 
            $mensagem = '<p class="error-message">Erro: ' . $e->getMessage() . '</p>'; 
        }
    }
}

// --- LÓGICA DE CARREGAMENTO (ATUALIZADA) ---
try {
    // 1. Busca todos os alunos (para o select)
    $query_alunos = $pdo->query("SELECT a.id, a.nome_completo, t.nome_turma FROM alunos a LEFT JOIN turmas t ON a.id_turma = t.id ORDER BY a.nome_completo");
    $alunos = $query_alunos->fetchAll();
    
    // 2. (NOVO) Busca IDs de alunos que JÁ TÊM DVA
    $query_dvas = $pdo->query("SELECT id_aluno FROM dvas");
    // Pega apenas a coluna 'id_aluno' como um array simples
    $lista_alunos_com_dva = $query_dvas->fetchAll(PDO::FETCH_COLUMN);
    // Converte para JSON para o JavaScript poder ler
    $json_alunos_com_dva = json_encode($lista_alunos_com_dva);

} catch (PDOException $e) { 
    $alunos = []; 
    $mensagem .= '<p class="error-message">Erro ao carregar alunos.</p>'; 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar DVA</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header><h1>Cadastrar DVA</h1></header>
    <nav>
        <a href="../painel.php">Início (Painel)</a>
        <a href="../aluno/cadastrar_aluno.php">Cadastrar Aluno</a>
        <a href="cadastrar_dva.php">Cadastrar DVA</a>
        <a href="../aluno/gerenciar_alunos.php" style="font-weight: bold;">Gerenciar Alunos</a>
        <a href="../passivo/gerenciar_passivo.php" style="font-weight: bold; color: #004a91;">Arquivo Passivo</a>
        
        <?php if ($tipo_usuario_logado == 'admin'): ?>
            <a href="../usuario/gerenciar_usuarios.php" style="color: #d9534f; font-weight: bold;">Gerenciar Usuários</a>
        <?php endif; ?>
        
        <span>Olá, <?php echo htmlspecialchars($nome_usuario_logado); ?>!</span>
        <a href="../logout.php" class="logout" style="float: right;">Sair</a>
    </nav>
    <main>
        <div class="sistema">
            <?php echo $mensagem; ?>
            
            <form action="cadastrar_dva.php" method="POST" id="formCadastrarDva">
                <div>
                    <label for="filtroAluno">Pesquisar Aluno:</label>
                    <input type="search" id="filtroAluno" placeholder="Digite o nome do aluno para filtrar a lista..." class="sistema">
                </div>
                <div>
                    <label for="selectAluno">Aluno:</label>
                    <select id="selectAluno" name="id_aluno" required size="8">
                        <option value="">Selecione um aluno...</option>
                        <?php foreach ($alunos as $aluno): ?>
                            <option value="<?php echo $aluno['id']; ?>">
                                <?php echo htmlspecialchars($aluno['nome_completo']); ?> 
                                (<?php echo htmlspecialchars($aluno['nome_turma'] ?? 'Sem turma'); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="data_vencimento">Data de Vencimento:</label>
                    <input type="date" id="data_vencimento" name="data_vencimento" required>
                </div>
                <div>
                    <label for="observacao">Observações:</label>
                    <textarea id="observacao" name="observacao" rows="3"></textarea>
                </div>
                <div><button type="submit">Registrar / Atualizar DVA</button></div>
            </form>
        </div>
    </main>
    
    <script>
        document.getElementById('filtroAluno').addEventListener('keyup', function() {
            let filtro = this.value.toLowerCase();
            let select = document.getElementById('selectAluno');
            for (let i = 1; i < select.options.length; i++) {
                let option = select.options[i];
                option.style.display = option.text.toLowerCase().includes(filtro) ? '' : 'none';
            }
        });
    </script>

    <script>
        // Injeta a lista de IDs de alunos (do PHP) para o JavaScript
        const alunosComDVA = <?php echo $json_alunos_com_dva; ?>;

        // Pega o formulário e o select
        const formDVA = document.getElementById('formCadastrarDva');
        const selectAluno = document.getElementById('selectAluno');

        // Adiciona um "escutador" para o evento de SUBMIT (envio)
        formDVA.addEventListener('submit', function(event) {
            
            // Pega o ID do aluno selecionado e converte para número
            let alunoIdSelecionado = parseInt(selectAluno.value, 10);

            // Verifica se o ID selecionado ESTÁ na lista de alunos que já têm DVA
            if (alunosComDVA.includes(alunoIdSelecionado)) {
                
                // Se estiver, mostra a caixa de confirmação
                let confirmou = confirm("Este aluno já possui uma DVA registrada.\n\nDeseja realmente substituí-la por esta nova data?");
                
                // Se o usuário clicar em "Cancelar" (false), impede o envio do formulário
                if (!confirmou) {
                    event.preventDefault(); // Impede o formulário de ser enviado
                }
                // Se o usuário clicar em "OK", o script não faz nada
                // e o formulário é enviado normalmente.
            }
        });
    </script>
</body>
</html>