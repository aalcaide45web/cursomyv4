<?php
require_once 'app/Controllers/DashboardController.php';

echo "🧪 Probando APIs del Dashboard\n\n";

try {
    $controller = new DashboardController();
    
    echo "📊 Probando getStats():\n";
    ob_start();
    $controller->getStats();
    $statsResponse = ob_get_clean();
    echo $statsResponse . "\n\n";
    
    echo "📚 Probando getCourses():\n";
    ob_start();
    $controller->getCourses();
    $coursesResponse = ob_get_clean();
    echo $coursesResponse . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}
