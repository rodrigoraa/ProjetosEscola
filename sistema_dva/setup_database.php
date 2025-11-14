<?php
/**
 * SETUP_BANCO.PHP
 * Execute este script UMA VEZ para criar o banco e os usuários iniciais.
 * DEPOIS, APAGUE ESTE ARQUIVO!
 */

// Define o caminho para o arquivo do banco
$dbFile = __DIR__ . '/database/escola.db';

// Tenta criar o diretório 'database' se ele não existir
if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0755, true);
}

// Conexão
try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');

} catch (PDOException $e) {
    die("Erro ao conectar ou criar o banco de dados: " . $e->getMessage());
}

// SQL para criar as tabelas
$sql_schema = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha TEXT NOT NULL,
        tipo TEXT NOT NULL DEFAULT 'funcionario' -- 'funcionario' ou 'admin'
    );

    CREATE TABLE IF NOT EXISTS turmas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_turma TEXT NOT NULL,
        ano_letivo INTEGER NOT NULL
    );

    CREATE TABLE IF NOT EXISTS alunos (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome_completo TEXT NOT NULL,
        data_nascimento TEXT NOT NULL,
        id_turma INTEGER,
        FOREIGN KEY (id_turma) REFERENCES turmas(id) ON DELETE SET NULL
    );

    CREATE TABLE IF NOT EXISTS dvas (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        id_aluno INTEGER NOT NULL,
        id_usuario_registro INTEGER NOT NULL,
        data_vencimento TEXT NOT NULL,
        observacao TEXT,
        data_emissao TEXT, -- Coluna antiga (não usada, mas mantida para evitar erros)
        FOREIGN KEY (id_aluno) REFERENCES alunos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id) ON DELETE SET NULL
    );
";

try {
    $pdo->exec($sql_schema);
    echo "Tabelas criadas com sucesso!<br>";

    // --- Inserir Usuários Padrão ---
    $email_admin = 'admin@escola.com';
    $senha_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrador Mestre', $email_admin, $senha_admin, 'admin']);

    $email_func = 'func@escola.com';
    $senha_func = password_hash('func123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Funcionário Padrão', $email_func, $senha_func, 'funcionario']);
    
    echo "Usuários padrão ('admin' e 'funcionario') criados/verificados!<br>";
    echo "<h2>SETUP CONCLUÍDO. APAGUE ESTE ARQUIVO AGORA!</h2>";
    echo "<p>Use o arquivo 'ajustar_turmas.php' para popular as turmas.</p>";

} catch (PDOException $e) {
    die("Erro ao executar o setup: " . $e->getMessage());
}
?>