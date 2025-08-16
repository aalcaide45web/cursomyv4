<?php declare(strict_types=1);

class FileScanner
{
    private array $config;
    private PDO $db;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/env.php';
        $this->db = DB::getInstance();
    }
    
    /**
     * Escanea el directorio de uploads y detecta cambios
     */
    public function scanDirectory(string $mode = 'incremental'): array
    {
        $uploadsPath = $this->config['UPLOADS_PATH'];
        $changes = [
            'added' => [],
            'modified' => [],
            'deleted' => [],
            'unchanged' => []
        ];
        
        if (!is_dir($uploadsPath)) {
            return $changes;
        }
        
        $this->ensureScanIndexTable();
        
        if ($mode === 'rebuild') {
            // Modo rebuild: escanea todo desde cero
            $changes = $this->scanAllFiles($uploadsPath);
        } else {
            // Modo incremental: solo archivos modificados
            $changes = $this->scanIncremental($uploadsPath);
        }
        
        return $changes;
    }
    
    /**
     * Escanea todos los archivos (modo rebuild)
     */
    private function scanAllFiles(string $uploadsPath): array
    {
        $changes = [
            'added' => [],
            'modified' => [],
            'deleted' => [],
            'unchanged' => []
        ];
        
        $files = $this->getAllVideoFiles($uploadsPath);
        
        foreach ($files as $file) {
            $relativePath = str_replace($uploadsPath . DIRECTORY_SEPARATOR, '', $file);
            $fileInfo = $this->getFileInfo($file);
            
            $changes['added'][] = [
                'path' => $relativePath,
                'full_path' => $file,
                'info' => $fileInfo
            ];
        }
        
        return $changes;
    }
    
    /**
     * Escanea solo archivos modificados (modo incremental)
     */
    private function scanIncremental(string $uploadsPath): array
    {
        $changes = [
            'added' => [],
            'modified' => [],
            'deleted' => [],
            'unchanged' => []
        ];
        
        $files = $this->getAllVideoFiles($uploadsPath);
        $dbFiles = $this->getScannedFilesFromDB();
        
        foreach ($files as $file) {
            $relativePath = str_replace($uploadsPath . DIRECTORY_SEPARATOR, '', $file);
            $fileInfo = $this->getFileInfo($file);
            
            if (!isset($dbFiles[$relativePath])) {
                // Archivo nuevo
                $changes['added'][] = [
                    'path' => $relativePath,
                    'full_path' => $file,
                    'info' => $fileInfo
                ];
            } elseif ($this->hasFileChanged($relativePath, $fileInfo, $dbFiles[$relativePath])) {
                // Archivo modificado
                $changes['modified'][] = [
                    'path' => $relativePath,
                    'full_path' => $file,
                    'info' => $fileInfo
                ];
            } else {
                // Archivo sin cambios
                $changes['unchanged'][] = [
                    'path' => $relativePath,
                    'full_path' => $file,
                    'info' => $fileInfo
                ];
            }
        }
        
        // Detectar archivos eliminados
        foreach ($dbFiles as $dbPath => $dbInfo) {
            if (!file_exists($uploadsPath . DIRECTORY_SEPARATOR . $dbPath)) {
                $changes['deleted'][] = [
                    'path' => $dbPath,
                    'info' => $dbInfo
                ];
            }
        }
        
        return $changes;
    }
    
    /**
     * Obtiene todos los archivos de video del directorio
     */
    private function getAllVideoFiles(string $directory): array
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'webm'];
        $files = [];
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $extension = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
                if (in_array($extension, $videoExtensions)) {
                    $files[] = $file->getPathname();
                }
            }
        }
        
        return $files;
    }
    
    /**
     * Obtiene información del archivo
     */
    private function getFileInfo(string $filePath): array
    {
        $stat = stat($filePath);
        $hash = hash_file('md5', $filePath);
        
        return [
            'size' => $stat['size'],
            'mtime' => $stat['mtime'],
            'hash' => $hash,
            'extension' => strtolower(pathinfo($filePath, PATHINFO_EXTENSION))
        ];
    }
    
    /**
     * Verifica si un archivo ha cambiado
     */
    private function hasFileChanged(string $relativePath, array $currentInfo, array $dbInfo): bool
    {
        return $currentInfo['size'] !== $dbInfo['size'] || 
               $currentInfo['mtime'] !== $dbInfo['mtime'] || 
               $currentInfo['hash'] !== $dbInfo['hash'];
    }
    
    /**
     * Obtiene archivos escaneados de la base de datos
     */
    private function getScannedFilesFromDB(): array
    {
        $stmt = $this->db->prepare("SELECT file_path, file_size, file_mtime, file_hash FROM scan_index");
        $stmt->execute();
        
        $files = [];
        while ($row = $stmt->fetch()) {
            $files[$row['file_path']] = [
                'size' => $row['file_size'],
                'mtime' => $row['file_mtime'],
                'hash' => $row['file_hash']
            ];
        }
        
        return $files;
    }
    
    /**
     * Asegura que existe la tabla scan_index
     */
    private function ensureScanIndexTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS scan_index (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            file_path TEXT UNIQUE NOT NULL,
            file_size INTEGER NOT NULL,
            file_mtime INTEGER NOT NULL,
            file_hash TEXT NOT NULL,
            last_seen_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )";
        
        $this->db->exec($sql);
    }
    
    /**
     * Actualiza el índice de archivos escaneados
     */
    public function updateScanIndex(array $changes): void
    {
        $this->db->beginTransaction();
        
        try {
            // Insertar/actualizar archivos nuevos o modificados
            foreach (array_merge($changes['added'], $changes['modified']) as $file) {
                $stmt = $this->db->prepare("
                    INSERT OR REPLACE INTO scan_index (file_path, file_size, file_mtime, file_hash, last_seen_at)
                    VALUES (?, ?, ?, ?, datetime('now'))
                ");
                
                $stmt->execute([
                    $file['path'],
                    $file['info']['size'],
                    $file['info']['mtime'],
                    $file['info']['hash']
                ]);
            }
            
            // Eliminar archivos borrados
            foreach ($changes['deleted'] as $file) {
                $stmt = $this->db->prepare("DELETE FROM scan_index WHERE file_path = ?");
                $stmt->execute([$file['path']]);
            }
            
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}
