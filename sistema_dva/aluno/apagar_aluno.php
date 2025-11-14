<?php
// ATUALIZADO: ../app/
require_once '../app/protecao_admin.php'; // Só admin apaga aluno
require_once '../app/conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gerenciar_alunos.php'); // Correto (mesma pasta)
    exit();
}
$id_aluno_para_apagar = $_GET['id'];

try {
    $sql = "DELETE FROM alunos WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_aluno_para_apagar]);
    
    // ON DELETE CASCADE no banco de dados apagará as DVAs associadas
    
    header('Location: gerenciar_alunos.php'); // Correto (mesma pasta)
    exit();
} catch (PDOException $e) {
    die("Erro ao apagar o aluno: " . $e->getMessage());
}
?>