<?php declare(strict_types=1);

require_once __DIR__ . '/../Services/DB.php';
require_once __DIR__ . '/../Services/LessonMetadataManager.php';
require_once __DIR__ . '/../Repositories/CourseRepository.php';
require_once __DIR__ . '/../Repositories/LessonRepository.php';
require_once __DIR__ . '/../Repositories/SectionRepository.php';
require_once __DIR__ . '/../Repositories/AttachmentRepository.php';
require_once __DIR__ . '/../Repositories/NoteRepository.php';
require_once __DIR__ . '/../Repositories/CommentRepository.php';
require_once __DIR__ . '/../Repositories/ProgressRepository.php';

class PlayerController
{
    private CourseRepository $courseRepo;
    private LessonRepository $lessonRepo;
    private SectionRepository $sectionRepo;
    private AttachmentRepository $attachmentRepo;
    private NoteRepository $noteRepo;
    private CommentRepository $commentRepo;
    private ProgressRepository $progressRepo;
    private LessonMetadataManager $metadataManager;
    
    public function __construct()
    {
        $this->courseRepo = new CourseRepository();
        $this->lessonRepo = new LessonRepository();
        $this->sectionRepo = new SectionRepository();
        $this->attachmentRepo = new AttachmentRepository();
        $this->noteRepo = new NoteRepository();
        $this->commentRepo = new CommentRepository();
        $this->progressRepo = new ProgressRepository();
        $this->metadataManager = new LessonMetadataManager();
    }
    
    /**
     * Muestra la página del reproductor
     */
    public function index(): void
    {
        $lessonId = $_GET['id'] ?? null;
        
        if (!$lessonId) {
            $this->showError('ID de lección no especificado');
            return;
        }
        
        // Convertir a int para evitar errores de tipo
        $lessonId = (int) $lessonId;
        
        try {
            $lesson = $this->lessonRepo->findById($lessonId);
            if (!$lesson) {
                $this->showError('Lección no encontrada');
                return;
            }
            
            $section = $this->sectionRepo->findById($lesson['section_id']);
            $course = $this->courseRepo->findById($section['course_id']);
            $attachments = $this->attachmentRepo->findBySection($section['id']);
            $notes = $this->noteRepo->findByLesson($lessonId);
            $comments = $this->commentRepo->findByLesson($lessonId);
            $progress = $this->progressRepo->findByLesson($lessonId);
            
            $title = "{$lesson['name']} - {$course['name']}";
            $content = $this->renderPlayerView($lesson, $section, $course, $attachments, $notes, $comments, $progress);
            
            include __DIR__ . '/../Views/partials/layout.php';
            
        } catch (Exception $e) {
            $this->showError('Error cargando la lección: ' . $e->getMessage());
        }
    }
    
    /**
     * Renderiza la vista del reproductor
     */
    private function renderPlayerView(array $lesson, array $section, array $course, array $attachments, array $notes, array $comments, ?array $progress): string
    {
        $lastPosition = $progress ? $progress['last_t_seconds'] : 0;
        
        return file_get_contents(__DIR__ . '/../Views/pages/player.php');
    }
    
    /**
     * API: Obtener información de la lección
     */
    public function getLessonInfo(): void
    {
        try {
            $lessonId = $_GET['id'] ?? null;
            if (!$lessonId) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de lección requerido']);
                return;
            }
            
            // Convertir a int para evitar errores de tipo
            $lessonId = (int) $lessonId;
            
            $lesson = $this->lessonRepo->findById($lessonId);
            if (!$lesson) {
                http_response_code(404);
                echo json_encode(['error' => 'Lección no encontrada']);
                return;
            }
            
            $section = $this->sectionRepo->findById($lesson['section_id']);
            $course = $this->courseRepo->findById($section['course_id']);
            $attachments = $this->attachmentRepo->findBySection($section['id']);
            
            $response = [
                'lesson' => $lesson,
                'section' => $section,
                'course' => $course,
                'attachments' => $attachments
            ];
            
            header('Content-Type: application/json');
            echo json_encode($response);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Guardar progreso
     */
    public function saveProgress(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['lesson_id']) || !isset($data['position'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }
            
            $lessonId = (int) $data['lesson_id'];
            $position = (float) $data['position'];
            
            $this->progressRepo->saveProgress($lessonId, $position);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error guardando progreso: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Obtener notas de la lección
     */
    public function getNotes(): void
    {
        try {
            $lessonId = $_GET['lesson_id'] ?? null;
            if (!$lessonId) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de lección requerido']);
                return;
            }
            
            // Convertir a int para evitar errores de tipo
            $lessonId = (int) $lessonId;
            
            $notes = $this->noteRepo->findByLesson($lessonId);
            
            header('Content-Type: application/json');
            echo json_encode(['notes' => $notes]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error obteniendo notas: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Guardar nota
     */
    public function saveNote(): void
    {
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['lesson_id']) || !isset($data['timestamp']) || !isset($data['text'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Datos incompletos']);
                return;
            }
            
            // Obtener la lección para obtener el file_path
            $lesson = $this->lessonRepo->findById((int) $data['lesson_id']);
            if (!$lesson) {
                http_response_code(404);
                echo json_encode(['error' => 'Lección no encontrada']);
                return;
            }
            
            // Guardar en la base de datos
            $noteId = $this->noteRepo->create([
                'lesson_id' => (int) $data['lesson_id'],
                't_seconds' => (float) $data['timestamp'],
                'text' => $data['text']
            ]);
            
            // Guardar en el archivo de metadata
            $noteData = [
                'id' => $noteId,
                't_seconds' => (float) $data['timestamp'],
                'text' => $data['text']
            ];
            
            $this->metadataManager->addNote($lesson['file_path'], $noteData);
            
            $note = $this->noteRepo->findById($noteId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'note' => $note]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error guardando nota: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Obtener comentarios de la lección
     */
    public function getComments(): void
    {
        try {
            $lessonId = $_GET['lesson_id'] ?? null;
            if (!$lessonId) {
                http_response_code(400);
                echo json_encode(['error' => 'ID de lección requerido']);
                return;
            }
            
            // Convertir a int para evitar errores de tipo
            $lessonId = (int) $lessonId;
            
            $comments = $this->commentRepo->findByLesson($lessonId);
            
            header('Content-Type: application/json');
            echo json_encode(['comments' => $comments]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error obteniendo comentarios: ' . $e->getMessage()]);
        }
    }
    
    /**
     * API: Guardar comentario
     */
                         public function saveComment(): void
         {
             try {
                 $data = json_decode(file_get_contents('php://input'), true);

                 if (!isset($data['lesson_id']) || !isset($data['text'])) {
                     http_response_code(400);
                     echo json_encode(['error' => 'Datos incompletos']);
                     return;
                 }

                 // Obtener la lección para obtener el file_path
                 $lesson = $this->lessonRepo->findById((int) $data['lesson_id']);
                 if (!$lesson) {
                     http_response_code(404);
                     echo json_encode(['error' => 'Lección no encontrada']);
                     return;
                 }

                 // Guardar en la base de datos
                 $commentId = $this->commentRepo->create([
                     'lesson_id' => (int) $data['lesson_id'],
                     'text' => $data['text'],
                     't_seconds' => $data['timestamp'] ?? null
                 ]);

                 // Guardar en el archivo de metadata
                 $commentData = [
                     'id' => $commentId,
                     'text' => $data['text'],
                     't_seconds' => $data['timestamp'] ?? null
                 ];
                 
                 $this->metadataManager->addComment($lesson['file_path'], $commentData);

                 $comment = $this->commentRepo->findById($commentId);

                 header('Content-Type: application/json');
                 echo json_encode(['success' => true, 'comment' => $comment]);

             } catch (Exception $e) {
                 http_response_code(500);
                 echo json_encode(['error' => 'Error guardando comentario: ' . $e->getMessage()]);
             }
         }
        
        public function updateNote(): void
        {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['note_id']) || !isset($data['text'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos incompletos']);
                    return;
                }

                $noteId = (int) $data['note_id'];
                $text = $data['text'];

                // Obtener la nota para obtener el lesson_id
                $note = $this->noteRepo->findById($noteId);
                if (!$note) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Nota no encontrada']);
                    return;
                }

                // Obtener la lección para obtener el file_path
                $lesson = $this->lessonRepo->findById($note['lesson_id']);
                if (!$lesson) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lección no encontrada']);
                    return;
                }

                // Actualizar en la base de datos
                $this->noteRepo->update($noteId, ['text' => $text]);
                
                // Actualizar en el archivo de metadata
                $this->metadataManager->updateNote($lesson['file_path'], $noteId, $text);
                
                $updatedNote = $this->noteRepo->findById($noteId);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'note' => $updatedNote]);

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error actualizando nota: ' . $e->getMessage()]);
            }
        }
        
        public function deleteNote(): void
        {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['note_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de nota requerido']);
                    return;
                }

                $noteId = (int) $data['note_id'];

                // Obtener la nota para obtener el lesson_id
                $note = $this->noteRepo->findById($noteId);
                if (!$note) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Nota no encontrada']);
                    return;
                }

                // Obtener la lección para obtener el file_path
                $lesson = $this->lessonRepo->findById($note['lesson_id']);
                if (!$lesson) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lección no encontrada']);
                    return;
                }

                // Eliminar de la base de datos
                $this->noteRepo->delete($noteId);
                
                // Eliminar del archivo de metadata
                $this->metadataManager->deleteNote($lesson['file_path'], $noteId);

                header('Content-Type: application/json');
                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error eliminando nota: ' . $e->getMessage()]);
            }
        }
        
        public function updateComment(): void
        {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['comment_id']) || !isset($data['text'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Datos incompletos']);
                    return;
                }

                $commentId = (int) $data['comment_id'];
                $text = $data['text'];

                // Obtener el comentario para obtener el lesson_id
                $comment = $this->commentRepo->findById($commentId);
                if (!$comment) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Comentario no encontrado']);
                    return;
                }

                // Obtener la lección para obtener el file_path
                $lesson = $this->lessonRepo->findById($comment['lesson_id']);
                if (!$lesson) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lección no encontrada']);
                    return;
                }

                // Actualizar en la base de datos
                $this->commentRepo->update($commentId, ['text' => $text]);
                
                // Actualizar en el archivo de metadata
                $this->metadataManager->updateComment($lesson['file_path'], $commentId, $text);
                
                $updatedComment = $this->commentRepo->findById($commentId);

                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'comment' => $updatedComment]);

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error actualizando comentario: ' . $e->getMessage()]);
            }
        }
        
        public function deleteComment(): void
        {
            try {
                $data = json_decode(file_get_contents('php://input'), true);

                if (!isset($data['comment_id'])) {
                    http_response_code(400);
                    echo json_encode(['error' => 'ID de comentario requerido']);
                    return;
                }

                $commentId = (int) $data['comment_id'];

                // Obtener el comentario para obtener el lesson_id
                $comment = $this->commentRepo->findById($commentId);
                if (!$comment) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Comentario no encontrado']);
                    return;
                }

                // Obtener la lección para obtener el file_path
                $lesson = $this->lessonRepo->findById($comment['lesson_id']);
                if (!$lesson) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Lección no encontrada']);
                    return;
                }

                // Eliminar de la base de datos
                $this->commentRepo->delete($commentId);
                
                // Eliminar del archivo de metadata
                $this->metadataManager->deleteComment($lesson['file_path'], $commentId);

                header('Content-Type: application/json');
                echo json_encode(['success' => true]);

            } catch (Exception $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error eliminando comentario: ' . $e->getMessage()]);
            }
        }
        
        /**
         * Sirve archivos de video
         */
        public function serveVideo(string $path): void
        {
            try {
                // Decodificar la ruta URL (puede estar doble-codificada)
                $decodedPath = urldecode($path);
                // Si aún hay caracteres codificados, decodificar de nuevo
                if (strpos($decodedPath, '%') !== false) {
                    $decodedPath = urldecode($decodedPath);
                }
                
                // Buscar la lección en la BD usando la ruta decodificada
                $lesson = $this->lessonRepo->findByFilePath($decodedPath);
                
                if (!$lesson) {
                    // Si no se encuentra por la ruta exacta, intentar buscar por similitud
                    $lessons = $this->lessonRepo->findAll();
                    $foundLesson = null;
                    
                    foreach ($lessons as $l) {
                        // Normalizar ambas rutas para comparación
                        $normalizedDbPath = str_replace('\\', '/', $l['file_path']);
                        $normalizedUrlPath = str_replace('\\', '/', $decodedPath);
                        
                        if ($normalizedDbPath === $normalizedUrlPath) {
                            $foundLesson = $l;
                            break;
                        }
                    }
                    
                    if (!$foundLesson) {
                        http_response_code(404);
                        echo "Lección no encontrada para la ruta: {$decodedPath}";
                        return;
                    }
                    
                    $lesson = $foundLesson;
                }
                
                // Usar la ruta de la BD (que sabemos que es correcta)
                $fullPath = 'C:\\xampp\\htdocs\\cursomyV3\\uploads\\' . $lesson['file_path'];
                
                // Debug: mostrar la ruta que se está construyendo
                error_log("🎬 Ruta original recibida: {$path}");
                error_log("🎬 Ruta decodificada: {$decodedPath}");
                error_log("🎬 Ruta de la BD: {$lesson['file_path']}");
                error_log("🎬 Ruta del video: {$fullPath}");
                
                // Verificar que el archivo existe y es un video
                if (!file_exists($fullPath)) {
                    http_response_code(404);
                    echo "Archivo no encontrado: {$lesson['file_path']}<br>";
                    echo "Ruta completa: {$fullPath}<br>";
                    echo "Ruta existe: " . (is_dir(dirname($fullPath)) ? 'Sí' : 'No') . "<br>";
                    echo "Contenido del directorio: " . implode(', ', scandir(dirname($fullPath)));
                    return;
                }
                
                // Verificar que es un archivo de video
                $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'webm'];
                $extension = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
                
                if (!in_array($extension, $videoExtensions)) {
                    http_response_code(400);
                    echo "Tipo de archivo no permitido";
                    return;
                }
                
                // Obtener información del archivo
                $fileSize = filesize($fullPath);
                $mimeType = $this->getMimeType($extension);
                
                // Configurar headers para streaming de video
                header('Content-Type: ' . $mimeType);
                header('Content-Length: ' . $fileSize);
                header('Accept-Ranges: bytes');
                header('Cache-Control: public, max-age=31536000');
                
                // Leer y enviar el archivo
                readfile($fullPath);
                
            } catch (Exception $e) {
                http_response_code(500);
                echo "Error sirviendo video: " . $e->getMessage();
            }
        }
        
        /**
         * Obtiene el MIME type para extensiones de video
         */
        private function getMimeType(string $extension): string
        {
            $mimeTypes = [
                'mp4' => 'video/mp4',
                'avi' => 'video/x-msvideo',
                'mov' => 'video/quicktime',
                'mkv' => 'video/x-matroska',
                'wmv' => 'video/x-ms-wmv',
                'flv' => 'video/x-flv',
                'webm' => 'video/webm'
            ];
            
            return $mimeTypes[$extension] ?? 'application/octet-stream';
        }
    
    private function showError(string $message): void
    {
        http_response_code(404);
        echo "<h1>Error</h1><p>{$message}</p>";
    }
}
