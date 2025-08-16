<?php declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
$config = require __DIR__ . '/../config/env.php';
$dbPath = $config['DB_PATH'];

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla attachment si no existe
    $sql = "CREATE TABLE IF NOT EXISTS attachment (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        section_id INTEGER NOT NULL,
        filename TEXT NOT NULL,
        file_path TEXT NOT NULL,
        file_size INTEGER NOT NULL,
        file_type TEXT NOT NULL,
        mime_type TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (section_id) REFERENCES section(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sql);
    
    // Crear índices
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attachment_section ON attachment(section_id)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_attachment_filename ON attachment(filename)");
    
    echo "✅ Tabla 'attachment' creada/verificada exitosamente\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
