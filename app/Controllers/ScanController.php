<?php declare(strict_types=1);

require_once __DIR__ . '/../Services/DB.php';
require_once __DIR__ . '/../Services/FileScanner.php';
require_once __DIR__ . '/../Services/CourseImporter.php';
require_once __DIR__ . '/../Repositories/AttachmentRepository.php';
require_once __DIR__ . '/../Lib/JsonResponse.php';

class ScanController
{
    private FileScanner $fileScanner;
    private CourseImporter $courseImporter;
    
    public function __construct()
    {
        $this->fileScanner = new FileScanner();
        $this->courseImporter = new CourseImporter();
    }
    
    /**
     * Escaneo incremental - solo archivos nuevos/modificados
     */
    public function incremental(): void
    {
        try {
            // Escanear directorio
            $changes = $this->fileScanner->scanDirectory('incremental');
            
            // Importar cambios
            $importResults = $this->courseImporter->importFiles($changes);
            
            // Actualizar índice de escaneo
            $this->fileScanner->updateScanIndex($changes);
            
            // Preparar respuesta
            $response = [
                'success' => true,
                'mode' => 'incremental',
                'scan_results' => [
                    'added' => count($changes['added']),
                    'modified' => count($changes['modified']),
                    'deleted' => count($changes['deleted']),
                    'unchanged' => count($changes['unchanged'])
                ],
                'import_results' => $importResults,
                'message' => 'Escaneo incremental completado'
            ];
            
            JsonResponse::ok($response);
            
        } catch (Exception $e) {
            JsonResponse::error('Error durante escaneo incremental: ' . $e->getMessage());
        }
    }
    
    /**
     * Escaneo completo - rebuild desde cero
     */
    public function rebuild(): void
    {
        try {
            error_log("=== INICIANDO REBUILD ===");
            
            // Escanear directorio completo
            $changes = $this->fileScanner->scanDirectory('rebuild');
            error_log("Archivos escaneados: " . count(array_merge($changes['added'], $changes['modified'])));
            
            // Importar todos los archivos
            $importResults = $this->courseImporter->importFiles($changes);
            error_log("Importación completada. Archivos importados: " . count($importResults['imported']));
            
            // Actualizar índice de escaneo
            $this->fileScanner->updateScanIndex($changes);
            error_log("Índice de escaneo actualizado");
            
            // Preparar respuesta
            $response = [
                'success' => true,
                'mode' => 'rebuild',
                'scan_results' => [
                    'added' => count($changes['added']),
                    'modified' => count($changes['modified']),
                    'deleted' => count($changes['deleted']),
                    'unchanged' => count($changes['unchanged'])
                ],
                'import_results' => $importResults,
                'message' => 'Rebuild completado'
            ];
            
            error_log("Enviando respuesta JSON: " . json_encode($response));
            JsonResponse::ok($response);
            
        } catch (Exception $e) {
            error_log("❌ ERROR en rebuild: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            JsonResponse::error('Error durante rebuild: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener estado del escaneo
     */
    public function status(): void
    {
        try {
            $stats = $this->courseImporter->getImportStats();
            
            $response = [
                'success' => true,
                'stats' => $stats,
                'message' => 'Estado del escaneo obtenido'
            ];
            
            JsonResponse::ok($response);
            
        } catch (Exception $e) {
            JsonResponse::error('Error obteniendo estado: ' . $e->getMessage());
        }
    }
    
    /**
     * Obtener archivos escaneados
     */
    public function scannedFiles(): void
    {
        try {
            $db = DB::getInstance();
            $stmt = $db->prepare("
                SELECT file_path, file_size, file_mtime, file_hash, last_seen_at 
                FROM scan_index 
                ORDER BY last_seen_at DESC
            ");
            $stmt->execute();
            
            $files = $stmt->fetchAll();
            
            $response = [
                'success' => true,
                'files' => $files,
                'count' => count($files),
                'message' => 'Archivos escaneados obtenidos'
            ];
            
            JsonResponse::ok($response);
            
        } catch (Exception $e) {
            JsonResponse::error('Error obteniendo archivos escaneados: ' . $e->getMessage());
        }
    }
}
