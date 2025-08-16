<?php
require_once 'app/Services/DB.php';

try {
    $db = DB::getInstance();
    
    // Contar cursos
    $stmt = $db->query('SELECT COUNT(*) as total FROM course');
    $courses = $stmt->fetch()['total'];
    echo "📚 Cursos: $courses\n";
    
    // Contar secciones
    $stmt = $db->query('SELECT COUNT(*) as total FROM section');
    $sections = $stmt->fetch()['total'];
    echo "📁 Secciones: $sections\n";
    
    // Contar lecciones
    $stmt = $db->query('SELECT COUNT(*) as total FROM lesson');
    $lessons = $stmt->fetch()['total'];
    echo "🎥 Lecciones: $lessons\n";
    
    // Contar topics
    $stmt = $db->query('SELECT COUNT(*) as total FROM topic');
    $topics = $stmt->fetch()['total'];
    echo "🏷️ Topics: $topics\n";
    
    // Contar instructores
    $stmt = $db->query('SELECT COUNT(*) as total FROM instructor');
    $instructors = $stmt->fetch()['total'];
    echo "👨‍🏫 Instructores: $instructors\n";
    
    // Mostrar algunos cursos
    echo "\n📋 Cursos disponibles:\n";
    $stmt = $db->query('SELECT c.name, t.name as topic, i.name as instructor FROM course c JOIN topic t ON c.topic_id = t.id JOIN instructor i ON c.instructor_id = i.id');
    while ($row = $stmt->fetch()) {
        echo "- {$row['name']} (Tema: {$row['topic']}, Instructor: {$row['instructor']})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
