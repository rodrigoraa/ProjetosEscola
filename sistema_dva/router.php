<?php
// router.php (Arquivo Raiz)

$uri_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// --- Redirecionar a raiz (/) para o login.php ---
if ($uri_path === '/') {
    header('Location: /login.php');
    exit;
}

$requested_file = __DIR__ . $uri_path;

// --- REGRA DE SEGURANÇA ---
// Bloqueia acesso direto às pastas 'app' e 'database'
if (strpos($uri_path, '/database/') === 0 || strpos($uri_path, '/app/') === 0) {
    http_response_code(403); // HTTP 403 - Acesso Proibido
    echo "Acesso Proibido.";
    exit;
}

// --- REGRA PARA ARQUIVOS EXISTENTES ---
if (file_exists($requested_file) && !is_dir($requested_file)) {
    // Diz ao servidor PHP para entregar o arquivo como ele é
    return false;
}

// --- BLOCO DE DEPURAÇÃO (DEBUG) ---
// Se o arquivo não foi encontrado, mostra uma mensagem de erro clara
http_response_code(404);
if (strpos($uri_path, '.php') === false && $uri_path !== '/') {
    echo "<h2>Erro 404 - Depuração do Router</h2>";
    echo "<p>O servidor não encontrou o recurso (CSS, JS, Imagem?).</p>";
    echo "<p><b>URL pedida:</b> " . htmlspecialchars($uri_path) . "</p>";
    echo "<p><b>O router procurou este caminho no disco:</b></p>";
    echo "<pre>" . htmlspecialchars($requested_file) . "</pre>";
} else {
    echo "Página não encontrada: " . htmlspecialchars($uri_path);
}
?>