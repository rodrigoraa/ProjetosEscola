<?php
require_once '../app/protecao_admin.php'; // Só admin apaga DVA
require_once '../app/conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: ../painel.php'); // ATUALIZADO: ../
    exit();
}
$id_dva_para_apagar = $_GET['id'];

try {
    $sql = "DELETE FROM dvas WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_dva_para_apagar]);
    
    header('Location: ../painel.php'); // ATUALIZADO: ../
    exit();
} catch (PDOException $e) {
    die("Erro ao apagar o registro de DVA: " . $e->getMessage());
}
?>