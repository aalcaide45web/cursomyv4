<?php declare(strict_types=1);

require_once __DIR__ . '/../Services/DB.php';
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
    
    public function __construct()
    {
        $this->courseRepo = new CourseRepository();
        $this->lessonRepo = new LessonRepository();
        $this->sectionRepo = new SectionRepository();
        $this->attachmentRepo = new AttachmentRepository();
        $this->noteRepo = new NoteRepository();
        $this->commentRepo = new CommentRepository();
        $this->progressRepo = new ProgressRepository();
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
            
            $noteId = $this->noteRepo->create([
                'lesson_id' => (int) $data['lesson_id'],
                't_seconds' => (float) $data['timestamp'],
                'text' => $data['text']
            ]);
            
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
            
            $commentId = $this->commentRepo->create([
                'lesson_id' => (int) $data['lesson_id'],
                'text' => $data['text'],
                't_seconds' => $data['timestamp'] ?? null
            ]);
            
            $comment = $this->commentRepo->findById($commentId);
            
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'comment' => $comment]);
            
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Error guardando comentario: ' . $e->getMessage()]);
        }
    }
    
    private function showError(string $message): void
    {
        http_response_code(404);
        echo "<h1>Error</h1><p>{$message}</p>";
    }
}
