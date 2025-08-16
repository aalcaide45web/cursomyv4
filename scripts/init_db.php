<?php declare(strict_types=1);

require_once __DIR__ . '/../config/env.php';

$config = require __DIR__ . '/../config/env.php';
$dbPath = $config['DB_PATH'];

// Crear directorio de base de datos si no existe
$dbDir = dirname($dbPath);
if (!is_dir($dbDir)) {
    mkdir($dbDir, 0755, true);
}

try {
    // Conectar a SQLite (crea el archivo si no existe)
    $pdo = new PDO("sqlite:$dbPath");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conectado a SQLite: $dbPath\n";
    
    // Leer y ejecutar el esquema
    $schema = file_get_contents(__DIR__ . '/../database/schema.sql');
    $pdo->exec($schema);
    
    echo "✅ Esquema de base de datos creado exitosamente\n";
    
    // Insertar datos de ejemplo
    $pdo->exec("INSERT OR IGNORE INTO topic (name, slug) VALUES ('Programación', 'programacion')");
    $pdo->exec("INSERT OR IGNORE INTO instructor (name, slug) VALUES ('Juan Pérez', 'juan_perez')");
    
    echo "✅ Datos de ejemplo insertados\n";
    
    echo "\n🎉 Base de datos inicializada correctamente!\n";
    echo "📁 Ubicación: $dbPath\n";
    
} catch (PDOException $e) {
    echo "❌ Error al crear la base de datos: " . $e->getMessage() . "\n";
    exit(1);
}
