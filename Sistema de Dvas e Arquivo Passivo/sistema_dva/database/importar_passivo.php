<?php
require_once 'app/conexao.php';

// Função para remover acentos (para a coluna de ordenação)
function criar_nome_sort($str) {
    $str = str_replace(
        ['á', 'à', 'â', 'ã', 'ä', 'é', 'è', 'ê', 'ë', 'í', 'ì', 'î', 'ï', 'ó', 'ò', 'ô', 'õ', 'ö', 'ú', 'ù', 'û', 'ü', 'ç'],
        ['a', 'a', 'a', 'a', 'a', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'u', 'c'],
        strtolower($str)
    );
    return strtoupper($str); // Salva em maiúsculo para ordenação
}

$mensagem = ''; $erros = []; $sucessos = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["arquivo_csv"])) {
    
    $nome_arquivo = $_FILES["arquivo_csv"]["tmp_name"];
    
    if (($handle = fopen($nome_arquivo, "r")) !== FALSE) {
        
        // CORRIGIDO: Adicionado 5º parâmetro (escape) "\\"
        fgetcsv($handle, 1000, ";", '"', "\\"); // Ignora cabeçalho
        
        $pdo->beginTransaction(); 

        $sql = "INSERT INTO alunos_passivo (nome_completo, data_nascimento, numero, caixa, nome_sort) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        
        $linha_num = 1;
        
        // CORRIGIDO: Adicionado 5º parâmetro (escape) "\\"
        while (($dados = fgetcsv($handle, 1000, ";", '"', "\\")) !== FALSE) {
            $linha_num++;
            
            if (count($dados) >= 4) {
                $nome = trim($dados[0]);
                $data_nasc_original = trim($dados[1]);
                $numero = trim($dados[2]);
                $caixa = trim($dados[3]);
                
                // Gera o nome de ordenação
                $nome_de_sort = criar_nome_sort($nome);

                $data_nasc_formatada = null;
                try {
                    if (!empty($data_nasc_original)) { $data_nasc_formatada = DateTime::createFromFormat('d/m/Y', $data_nasc_original)->format('Y-m-d'); }
                } catch (Exception $e) { $data_nasc_formatada = null; }

                try {
                    // Envia 5 valores (com o nome_sort)
                    $stmt->execute([$nome, $data_nasc_formatada, $numero, $caixa, $nome_de_sort]);
                    $sucessos++;
                } catch (PDOException $e) {
                    $erros[] = "Linha $linha_num: Erro ao inserir " . htmlspecialchars($nome) . " - " . $e->getMessage();
                }
            } else {
                $erros[] = "Linha $linha_num: Formato inválido (esperava 4 colunas).";
            }
        }
        fclose($handle);
        $pdo->commit(); 
        
        $mensagem = "<p class='success-message'>Importação concluída! $sucessos registros inseridos.</p>";
        if (!empty($erros)) {
            $mensagem .= "<p class'error-message'>Alguns erros ocorreram:</p><ul>";
            foreach($erros as $erro) { $mensagem .= "<li>$erro</li>"; }
            $mensagem .= "</ul>";
        }
    } else {
        $mensagem = "<p class'error-message'>Não foi possível abrir o arquivo.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Importar Alunos Passivos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header><h1>Importar Alunos do Arquivo Passivo</h1></header>
    <nav>
        <a href="painel.php">Voltar ao Painel</a>
    </nav>
    <main>
        <form action="importar_passivo.php" method="POST" enctype="multipart/form-data" class="sistema">
            <h2>Importar de CSV</h2>
            <p><b>Instruções:</b></p>
            <ol>
                <li>Use um arquivo <b>CSV UTF-8 (separado por ponto e vírgula ';')</b>.</li>
                <li>Garanta que as colunas sejam: <b>A (Nome), B (Data Nasc.), C (Número), D (Nº Caixa)</b>.</li>
                <li>A primeira linha (cabeçalho) será ignorada.</li>
            </ol>
            
            <div style="margin-top: 20px;">
                <label for="arquivo_csv">Selecione o arquivo .csv:</label>
                <input type="file" id="arquivo_csv" name="arquivo_csv" accept=".csv" required>
            </div>

            <div>
                <button type="submit" style="background-color: #d9534f;">Importar Dados</button>
            </div>
        </form>

        <div style="margin-top: 20px;">
            <?php echo $mensagem; ?>
        </div>
    </main>
</body>
</html>