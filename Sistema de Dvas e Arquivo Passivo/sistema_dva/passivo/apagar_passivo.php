<?php
// Proteção de LOGIN (Qualquer funcionário pode apagar)
require_once '../app/protecao_login.php';
require_once '../app/conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gerenciar_passivo.php');
    exit();
}

$id_para_apagar = $_GET['id'];

try {
    $sql = "DELETE FROM alunos_passivo WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_para_apagar]);
    
    header('Location: gerenciar_passivo.php');
    exit();

} catch (PDOException $e) {
    die("Erro ao apagar o registro: " . $e->getMessage());
}
?>