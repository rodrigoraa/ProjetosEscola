<?php
/**
 * SETUP_BANCO.PHP
 * ATUALIZADO: Tabela DVA agora tem 'id_aluno' como UNIQUE.
 * Execute este script UMA VEZ para criar o banco.
 * DEPOIS, APAGUE ESTE ARQUIVO!
 */

$dbFile = __DIR__ . '/database/escola.db';
if (!is_dir(__DIR__ . '/database')) {
    mkdir(__DIR__ . '/database', 0755, true);
}

try {
    $pdo = new PDO("sqlite:" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $pdo->exec('PRAGMA foreign_keys = ON;');
} catch (PDOException $e) {
    die("Erro ao conectar ou criar o banco de dados: " . $e->getMessage());
}

// Apaga a tabela antiga de DVAs para garantir a nova regra
$pdo->exec("DROP TABLE IF EXISTS dvas;");

$sql_schema = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nome TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        senha TEXT NOT NULL,
        tipo TEXT NOT NULL DEFAULT 'funcionario'
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
        id_aluno INTEGER NOT NULL UNIQUE, -- <<< MUDANÇA IMPORTANTE AQUI
        id_usuario_registro INTEGER NOT NULL,
        data_vencimento TEXT NOT NULL,
        observacao TEXT,
        data_emissao TEXT, 
        FOREIGN KEY (id_aluno) REFERENCES alunos(id) ON DELETE CASCADE,
        FOREIGN KEY (id_usuario_registro) REFERENCES usuarios(id) ON DELETE SET NULL
    );
";

try {
    $pdo->exec($sql_schema);
    echo "Tabelas criadas com sucesso (Regra UNIQUE para DVAs aplicada)!<br>";

    // ... (Código de inserir usuários padrão) ...
    $email_admin = 'admin@escola.com';
    $senha_admin = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Administrador Mestre', $email_admin, $senha_admin, 'admin']);

    $email_func = 'func@escola.com';
    $senha_func = password_hash('func123', PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, ?)");
    $stmt->execute(['Funcionário Padrão', $email_func, $senha_func, 'funcionario']);
    
    echo "Usuários padrão criados/verificados!<br>";
    echo "<h2>SETUP CONCLUÍDO. APAGUE ESTE ARQUIVO!</h2>";

} catch (PDOException $e) {
    die("Erro ao executar o setup: " . $e->getMessage());
}
?>