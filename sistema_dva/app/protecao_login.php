<?php
// app/protecao_login.php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    // Atualizado: ../ para voltar uma pasta e encontrar o login.php
    header('Location: ../login.php');
    exit();
}

// Disponibiliza os dados da sessão
$usuario_id_logado = $_SESSION['usuario_id'];
$nome_usuario_logado = $_SESSION['usuario_nome'];
$tipo_usuario_logado = $_SESSION['usuario_tipo'];
?>