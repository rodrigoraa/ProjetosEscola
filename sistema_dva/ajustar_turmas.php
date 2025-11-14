<?php
/**
 * AJUSTAR_TURMAS.PHP
 * Execute este script UMA VEZ para limpar turmas antigas
 * e inserir as séries corretas (Fundamental e Médio).
 * DEPOIS, APAGUE ESTE ARQUIVO!
 */

require_once 'app/conexao.php'; // Busca a conexão na pasta app/

echo "<h1>Ajustando Turmas...</h1>";

try {
    // Inicia uma transação (para garantir que tudo corra bem)
    $pdo->beginTransaction();

    // 1. Apaga TODAS as turmas antigas (se houver)
    $pdo->exec("DELETE FROM turmas");
    echo "<p>Turmas antigas apagadas.</p>";

    // 2. Define as novas turmas
    $turmas_fund = [
        "1º Ano - Ens. Fundamental",
        "2º Ano - Ens. Fundamental",
        "3º Ano - Ens. Fundamental",
        "4º Ano - Ens. Fundamental",
        "5º Ano - Ens. Fundamental",
        "6º Ano - Ens. Fundamental",
        "7º Ano - Ens. Fundamental",
        "8º Ano - Ens. Fundamental",
        "9º Ano - Ens. Fundamental",
    ];

    $turmas_med = [
        "1º Ano - Ens. Médio",
        "2º Ano - Ens. Médio",
        "3º Ano - Ens. Médio",
    ];

    // Pega o ano atual para o registro
    $ano_letivo_atual = date('Y');

    // 3. Insere o Ensino Fundamental
    $stmt = $pdo->prepare("INSERT INTO turmas (nome_turma, ano_letivo) VALUES (?, ?)");
    foreach ($turmas_fund as $turma) {
        $stmt->execute([$turma, $ano_letivo_atual]);
    }
    echo "<p>Turmas do Ensino Fundamental (1º ao 9º) inseridas.</p>";

    // 4. Insere o Ensino Médio
    foreach ($turmas_med as $turma) {
        $stmt->execute([$turma, $ano_letivo_atual]);
    }
    echo "<p>Turmas do Ensino Médio (1º ao 3º) inseridas.</p>";
    
    // Confirma as mudanças no banco de dados
    $pdo->commit();

    echo "<h2>Ajuste de turmas concluído com sucesso!</h2>";
    echo "<h3>Pode apagar este arquivo agora.</h3>";

} catch (Exception $e) {
    // Se algo der errado, desfaz tudo
    $pdo->rollBack();
    die("Erro ao ajustar turmas: " . $e->getMessage());
}

?>