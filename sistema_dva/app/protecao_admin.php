<?php
// app/protecao_admin.php

// 1. Inclui a proteção de login normal
require_once __DIR__ . '/protecao_login.php';

// 2. Verifica se o usuário logado é ADMIN
if ($tipo_usuario_logado != 'admin') {
    die("Acesso negado. Esta área é restrita a administradores.");
}
?>