<?php
require_once '../app/protecao_admin.php';
require_once '../app/conexao.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: gerenciar_usuarios.php'); // Correto (mesma pasta)
    exit();
}
$id_usuario_para_apagar = $_GET['id'];

if ($id_usuario_para_apagar == $usuario_id_logado) {
    die("Erro: Você não pode apagar sua própria conta.");
}

try {
    $sql = "DELETE FROM usuarios WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_usuario_para_apagar]);
    
    header('Location: gerenciar_usuarios.php'); // Correto (mesma pasta)
    exit();
} catch (PDOException $e) {
    die("Erro ao apagar o usuário: " . $e->getMessage());
}
?>