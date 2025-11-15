<?php
session_start();
$_SESSION = array();
session_destroy();
header('Location: login.php'); // Correto (login.php está na raiz)
exit();
?>