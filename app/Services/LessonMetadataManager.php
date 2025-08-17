<?php declare(strict_types=1);

class LessonMetadataManager
{
    private string $uploadsPath;
    
    public function __construct()
    {
        $this->uploadsPath = 'C:\\xampp\\htdocs\\cursomyV3\\uploads';
    }
    
    /**
     * Obtiene la ruta del archivo de metadata para una lección
     */
    public function getMetadataPath(string $filePath): string
    {
        // Obtener el directorio de la lección (sin el nombre del archivo)
        $lessonDir = dirname($filePath);
        return $this->uploadsPath . '\\' . $lessonDir . '\\lesson_metadata.json';
    }
    
    /**
     * Carga la metadata de una lección desde el archivo JSON
     */
    public function loadMetadata(string $filePath): array
    {
        $metadataPath = $this->getMetadataPath($filePath);
        
        if (!file_exists($metadataPath)) {
            return [
                'notes' => [],
                'comments' => [],
                'last_updated' => null
            ];
        }
        
        try {
            $content = file_get_contents($metadataPath);
            $metadata = json_decode($content, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                error_log("Error decodificando JSON de metadata: " . json_last_error_msg());
                return [
                    'notes' => [],
                    'comments' => [],
                    'last_updated' => null
                ];
            }
            
            return $metadata;
        } catch (Exception $e) {
            error_log("Error cargando metadata: " . $e->getMessage());
            return [
                'notes' => [],
                'comments' => [],
                'last_updated' => null
            ];
        }
    }
    
    /**
     * Guarda la metadata de una lección en el archivo JSON
     */
    public function saveMetadata(string $filePath, array $metadata): bool
    {
        try {
            $metadataPath = $this->getMetadataPath($filePath);
            $lessonDir = dirname($metadataPath);
            
            // Crear el directorio si no existe
            if (!is_dir($lessonDir)) {
                mkdir($lessonDir, 0755, true);
            }
            
            // Agregar timestamp de última actualización
            $metadata['last_updated'] = date('Y-m-d H:i:s');
            
            // Guardar en archivo JSON
            $jsonContent = json_encode($metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            
            if ($jsonContent === false) {
                error_log("Error codificando JSON de metadata");
                return false;
            }
            
            $result = file_put_contents($metadataPath, $jsonContent);
            
            if ($result === false) {
                error_log("Error escribiendo archivo de metadata: {$metadataPath}");
                return false;
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error guardando metadata: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Agrega una nota a la metadata
     */
    public function addNote(string $filePath, array $note): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        // Generar ID único si no existe
        if (!isset($note['id'])) {
            $note['id'] = uniqid('note_', true);
        }
        
        $note['created_at'] = date('Y-m-d H:i:s');
        $note['updated_at'] = date('Y-m-d H:i:s');
        
        $metadata['notes'][] = $note;
        
        return $this->saveMetadata($filePath, $metadata);
    }
    
    /**
     * Actualiza una nota en la metadata
     */
    public function updateNote(string $filePath, string $noteId, string $newText): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        foreach ($metadata['notes'] as &$note) {
            if ($note['id'] === $noteId) {
                $note['text'] = $newText;
                $note['updated_at'] = date('Y-m-d H:i:s');
                return $this->saveMetadata($filePath, $metadata);
            }
        }
        
        return false;
    }
    
    /**
     * Elimina una nota de la metadata
     */
    public function deleteNote(string $filePath, string $noteId): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        $metadata['notes'] = array_filter($metadata['notes'], function($note) use ($noteId) {
            return $note['id'] !== $noteId;
        });
        
        return $this->saveMetadata($filePath, $metadata);
    }
    
    /**
     * Agrega un comentario a la metadata
     */
    public function addComment(string $filePath, array $comment): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        // Generar ID único si no existe
        if (!isset($comment['id'])) {
            $comment['id'] = uniqid('comment_', true);
        }
        
        $comment['created_at'] = date('Y-m-d H:i:s');
        $comment['updated_at'] = date('Y-m-d H:i:s');
        
        $metadata['comments'][] = $comment;
        
        return $this->saveMetadata($filePath, $metadata);
    }
    
    /**
     * Actualiza un comentario en la metadata
     */
    public function updateComment(string $filePath, string $commentId, string $newText): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        foreach ($metadata['comments'] as &$comment) {
            if ($comment['id'] === $commentId) {
                $comment['text'] = $newText;
                $comment['updated_at'] = date('Y-m-d H:i:s');
                return $this->saveMetadata($filePath, $metadata);
            }
        }
        
        return false;
    }
    
    /**
     * Elimina un comentario de la metadata
     */
    public function deleteComment(string $filePath, string $commentId): bool
    {
        $metadata = $this->loadMetadata($filePath);
        
        $metadata['comments'] = array_filter($metadata['comments'], function($comment) use ($commentId) {
            return $comment['id'] !== $commentId;
        });
        
        return $this->saveMetadata($filePath, $metadata);
    }
    
    /**
     * Sincroniza la metadata del archivo JSON con la base de datos
     * (para cuando se hace rebuild)
     */
    public function syncWithDatabase(string $filePath, int $lessonId, NoteRepository $noteRepo, CommentRepository $commentRepo): bool
    {
        try {
            $metadata = $this->loadMetadata($filePath);
            
            // Sincronizar notas
            foreach ($metadata['notes'] as $noteData) {
                // Verificar si la nota ya existe en la BD
                $existingNote = $noteRepo->findByLessonAndTimestamp($lessonId, $noteData['t_seconds']);
                
                if (!$existingNote) {
                    // Crear la nota en la BD
                    $noteRepo->create([
                        'lesson_id' => $lessonId,
                        't_seconds' => $noteData['t_seconds'],
                        'text' => $noteData['text']
                    ]);
                }
            }
            
            // Sincronizar comentarios
            foreach ($metadata['comments'] as $commentData) {
                // Verificar si el comentario ya existe en la BD
                $existingComment = $commentRepo->findByLessonAndText($lessonId, $commentData['text']);
                
                if (!$existingComment) {
                    // Crear el comentario en la BD
                    $commentRepo->create([
                        'lesson_id' => $lessonId,
                        'text' => $commentData['text'],
                        't_seconds' => $commentData['t_seconds'] ?? null
                    ]);
                }
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error sincronizando metadata con BD: " . $e->getMessage());
            return false;
        }
    }
}
