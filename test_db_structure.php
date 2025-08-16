<?php
require_once 'app/Services/DB.php';

try {
    $db = DB::getInstance();
    
    // Verificar estructura de la tabla course
    echo "🔍 Estructura de la tabla 'course':\n";
    $stmt = $db->query("PRAGMA table_info(course)");
    $columns = $stmt->fetchAll();
    
    foreach ($columns as $column) {
        echo "- {$column['name']} ({$column['type']}) - Nullable: " . ($column['notnull'] ? 'NO' : 'YES') . "\n";
    }
    
    echo "\n📊 Datos en la tabla 'course':\n";
    $stmt = $db->query("SELECT * FROM course");
    $courses = $stmt->fetchAll();
    
    foreach ($courses as $course) {
        echo "- ID: {$course['id']}, Nombre: {$course['name']}, is_deleted: " . ($course['is_deleted'] ?? 'NULL') . "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
