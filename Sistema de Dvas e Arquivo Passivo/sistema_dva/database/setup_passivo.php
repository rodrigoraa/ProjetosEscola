<?php
/**
 * SETUP_PASSIVO.PHP
 * ATUALIZADO com a coluna 'nome_sort' para ordenação.
 * Execute 1x APÓS o setup_banco.php e DEPOIS APAGUE.
 */

require_once 'app/conexao.php';

echo "<h1>Criando Tabela 'alunos_passivo'...</h1>";

// Apaga a tabela antiga, se existir, para garantir a nova estrutura
$pdo->exec("DROP TABLE IF EXISTS alunos_passivo;");

$sql = "
    CREATE TABLE alunos_passivo (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_completo TEXT NOT NULL,
        data_nascimento TEXT,
        numero TEXT,
        caixa TEXT NOT NULL,
        nome_sort TEXT -- Coluna para ordenação correta
    );
";

try {
    $pdo->exec($sql);
    echo "<h2>Tabela 'alunos_passivo' criada com a coluna 'nome_sort'!</h2>";
    echo "<h3>Pode apagar este arquivo.</h3>";
} catch (PDOException $e) {
    die("Erro ao criar tabela: " . $e->getMessage());
}
?>