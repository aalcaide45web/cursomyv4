<?php declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';
$config = require __DIR__ . '/../config/env.php';
$dbPath = $config['DB_PATH'];

try {
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "🔄 Actualizando tabla 'progress'...\n";
    
    // Verificar si las columnas ya existen
    $stmt = $pdo->query("PRAGMA table_info(progress)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'name');
    
    // Agregar columnas faltantes
    if (!in_array('total_watched_seconds', $columnNames)) {
        $pdo->exec("ALTER TABLE progress ADD COLUMN total_watched_seconds REAL DEFAULT 0");
        echo "✅ Columna 'total_watched_seconds' agregada\n";
    }
    
    if (!in_array('is_completed', $columnNames)) {
        $pdo->exec("ALTER TABLE progress ADD COLUMN is_completed INTEGER DEFAULT 0");
        echo "✅ Columna 'is_completed' agregada\n";
    }
    
    if (!in_array('last_seen_at', $columnNames)) {
        $pdo->exec("ALTER TABLE progress ADD COLUMN last_seen_at TEXT");
        echo "✅ Columna 'last_seen_at' agregada\n";
        
        // Copiar datos de updated_at a last_seen_at
        $pdo->exec("UPDATE progress SET last_seen_at = updated_at WHERE last_seen_at IS NULL");
        echo "✅ Datos copiados de 'updated_at' a 'last_seen_at'\n";
    }
    
    if (!in_array('created_at', $columnNames)) {
        $pdo->exec("ALTER TABLE progress ADD COLUMN created_at TEXT");
        echo "✅ Columna 'created_at' agregada\n";
        
        // Copiar datos de updated_at a created_at
        $pdo->exec("UPDATE progress SET created_at = updated_at WHERE created_at IS NULL");
        echo "✅ Datos copiados de 'updated_at' a 'created_at'\n";
    }
    
    if (!in_array('completed_at', $columnNames)) {
        $pdo->exec("ALTER TABLE progress ADD COLUMN completed_at TEXT");
        echo "✅ Columna 'completed_at' agregada\n";
    }
    
    echo "🎉 Tabla 'progress' actualizada exitosamente\n";
    
    // Mostrar estructura final
    echo "\n📋 Estructura final de la tabla 'progress':\n";
    $stmt = $pdo->query("PRAGMA table_info(progress)");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($columns as $column) {
        echo "  - {$column['name']} ({$column['type']})\n";
    }
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
