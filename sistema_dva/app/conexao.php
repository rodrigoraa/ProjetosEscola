<?php
// app/conexao.php

// Define o caminho para o arquivo do banco de dados
$dbFile = __DIR__ . '/../database/escola.db';
$dsn = "sqlite:$dbFile";

try {
    $pdo = new PDO($dsn);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage();
    die();
}
// A variável $pdo está pronta para uso
?>