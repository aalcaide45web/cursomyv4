<?php declare(strict_types=1);

require_once __DIR__ . '/DB.php';
require_once __DIR__ . '/LessonMetadataManager.php';
require_once __DIR__ . '/../Repositories/TopicRepository.php';
require_once __DIR__ . '/../Repositories/InstructorRepository.php';
require_once __DIR__ . '/../Repositories/CourseRepository.php';
require_once __DIR__ . '/../Repositories/SectionRepository.php';
require_once __DIR__ . '/../Repositories/LessonRepository.php';
require_once __DIR__ . '/../Repositories/AttachmentRepository.php';
require_once __DIR__ . '/../Repositories/NoteRepository.php';
require_once __DIR__ . '/../Repositories/CommentRepository.php';

class CourseImporter
{
    private array $config;
    private PDO $db;
    private TopicRepository $topicRepo;
    private InstructorRepository $instructorRepo;
    private CourseRepository $courseRepo;
    private SectionRepository $sectionRepo;
    private LessonRepository $lessonRepo;
    private AttachmentRepository $attachmentRepo;
    private LessonMetadataManager $metadataManager;
    private NoteRepository $noteRepo;
    private CommentRepository $commentRepo;
    
    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/env.php';
        $this->db = DB::getInstance();
        $this->topicRepo = new TopicRepository();
        $this->instructorRepo = new InstructorRepository();
        $this->courseRepo = new CourseRepository();
        $this->sectionRepo = new SectionRepository();
        $this->lessonRepo = new LessonRepository();
        $this->attachmentRepo = new AttachmentRepository();
        $this->metadataManager = new LessonMetadataManager();
        $this->noteRepo = new NoteRepository();
        $this->commentRepo = new CommentRepository();
    }
    
    /**
     * Importa archivos escaneados como cursos
     */
    public function importFiles(array $changes): array
    {
        $results = [
            'imported' => [],
            'errors' => [],
            'summary' => [
                'courses_created' => 0,
                'sections_created' => 0,
                'lessons_created' => 0,
                'topics_created' => 0,
                'instructors_created' => 0
            ]
        ];
        
        // Debug: mostrar archivos a procesar
        error_log("=== INICIANDO IMPORTACIÓN ===");
        error_log("Archivos a procesar: " . count(array_merge($changes['added'], $changes['modified'])));
        
        $this->db->beginTransaction();
        
        try {
            // Procesar archivos nuevos y modificados
            foreach (array_merge($changes['added'], $changes['modified']) as $file) {
                error_log("Procesando archivo: {$file['path']}");
                
                $importResult = $this->importFile($file);
                
                if ($importResult['success']) {
                    $results['imported'][] = $importResult['data'];
                    error_log("✅ Archivo importado exitosamente: {$file['path']}");
                } else {
                    $results['errors'][] = $importResult['error'];
                    error_log("❌ Error importando: {$file['path']} - {$importResult['error']}");
                }
            }
            
            $this->db->commit();
            error_log("=== IMPORTACIÓN COMPLETADA ===");
        } catch (Exception $e) {
            $this->db->rollback();
            $errorMsg = 'Error durante la importación: ' . $e->getMessage();
            $results['errors'][] = $errorMsg;
            error_log("❌ ERROR CRÍTICO: {$errorMsg}");
        }
        
        return $results;
    }
    
    /**
     * Importa un archivo individual
     */
    private function importFile(array $file): array
    {
        try {
            $pathParts = $this->parseFilePath($file['path']);
            
            // Estructura esperada: tema/instructor/curso/seccion/archivo
            if (count($pathParts) < 5) {
                return [
                    'success' => false,
                    'error' => "Estructura de archivo inválida: {$file['path']}"
                ];
            }
            
            // Crear o encontrar topic
            $topic = $this->topicRepo->findOrCreate($pathParts[0], $this->slugify($pathParts[0]));
            
            // Crear o encontrar instructor
            $instructor = $this->instructorRepo->findOrCreate($pathParts[1], $this->slugify($pathParts[1]));
            
            // Crear o encontrar curso
            $course = $this->findOrCreateCourse($pathParts[2], $topic['id'], $instructor['id']);
            
            // Crear o encontrar sección
            $section = $this->findOrCreateSection($pathParts[3], $course['id']);
            
            // Determinar si es video (clase) o recurso adjunto
            $isVideo = $this->isVideoFile($file['path']);
            
            if ($isVideo) {
                // Es una CLASE/LECCIÓN
                $lesson = $this->findOrCreateLesson($pathParts[4], $section['id'], $file['path']);
                
                // Sincronizar metadata del archivo JSON con la base de datos
                $this->syncLessonMetadata($file['path'], $lesson['id']);
            } else {
                // Es un RECURSO ADJUNTO - no crear lección, solo registrar
                $this->registerAttachment($file['path'], $section['id'], $pathParts[4]);
            }
            
            return [
                'success' => true,
                'data' => [
                    'topic' => $topic,
                    'instructor' => $instructor,
                    'course' => $course,
                    'section' => $section,
                    'is_video' => $isVideo,
                    'filename' => $pathParts[4]
                ],
                'summary' => [
                    'courses_created' => 0,
                    'sections_created' => 0,
                    'lessons_created' => 0,
                    'topics_created' => 0,
                    'instructors_created' => 0
                ]
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => "Error importando {$file['path']}: " . $e->getMessage()
            ];
        }
    }
    
    /**
     * Parsea la ruta del archivo en componentes
     */
    private function parseFilePath(string $filePath): array
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $filePath);
        
        // Remover la extensión del archivo
        $filename = array_pop($pathParts);
        $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
        $pathParts[] = $filenameWithoutExt;
        
        return $pathParts;
    }
    
    /**
     * Encuentra o crea un curso
     */
    private function findOrCreateCourse(string $courseName, int $topicId, int $instructorId): array
    {
        $courseSlug = $this->slugify($courseName);
        
        // Buscar curso existente
        $existingCourse = $this->courseRepo->findBySlug($courseSlug);
        if ($existingCourse) {
            return $existingCourse;
        }
        
        // Crear nuevo curso
        $courseId = $this->courseRepo->create([
            'topic_id' => $topicId,
            'instructor_id' => $instructorId,
            'name' => $courseName,
            'slug' => $courseSlug
        ]);
        
        return $this->courseRepo->findById($courseId);
    }
    
    /**
     * Encuentra o crea una sección
     */
    private function findOrCreateSection(string $sectionName, int $courseId): array
    {
        // Extraer número de orden de la sección (ej: "01 - Introducción" -> 1)
        $orderIndex = $this->extractOrderNumber($sectionName);
        
        // Buscar sección existente
        $sections = $this->sectionRepo->findByCourse($courseId);
        foreach ($sections as $section) {
            if ($section['name'] === $sectionName) {
                return $section;
            }
        }
        
        // Crear nueva sección con el orden extraído
        $sectionId = $this->sectionRepo->create($courseId, $sectionName, $orderIndex);
        
        return $this->sectionRepo->findById($sectionId);
    }
    
    /**
     * Encuentra o crea una lección
     */
    private function findOrCreateLesson(string $lessonName, int $sectionId, string $filePath): array
    {
        // Buscar lección existente por file_path
        $existingLesson = $this->lessonRepo->findByFilePath($filePath);
        if ($existingLesson) {
            // Actualizar información si es necesario
            $this->lessonRepo->update($existingLesson['id'], ['name' => $lessonName]);
            return $existingLesson;
        }
        
        // Extraer número de orden de la lección (ej: "01 - Bienvenida" -> 1)
        $orderIndex = $this->extractOrderNumber($lessonName);
        
        // Crear nueva lección con el orden extraído
        $lessonId = $this->lessonRepo->create($sectionId, $lessonName, $filePath, $orderIndex);
        
        return $this->lessonRepo->findById($lessonId);
    }
    
    /**
     * Extrae el número de orden de un nombre (ej: "01 - Introducción" -> 1)
     */
    private function extractOrderNumber(string $name): int
    {
        // Buscar patrón: número seguido de guión o punto
        if (preg_match('/^(\d+)[\s\-\.]+/', $name, $matches)) {
            return (int) $matches[1];
        }
        
        // Si no hay numeración, usar 999 para que vaya al final
        return 999;
    }
    
    /**
     * Convierte texto a slug
     */
    private function slugify(string $text): string
    {
        // Convertir a minúsculas
        $text = strtolower($text);
        
        // Reemplazar caracteres especiales
        $text = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $text);
        
        // Reemplazar espacios y caracteres especiales con guiones
        $text = preg_replace('/[^a-z0-9]+/', '-', $text);
        
        // Remover guiones al inicio y final
        $text = trim($text, '-');
        
        return $text;
    }
    
    /**
     * Obtiene estadísticas de importación
     */
    public function getImportStats(): array
    {
        return [
            'total_courses' => $this->courseRepo->count(),
            'total_sections' => $this->sectionRepo->count(),
            'total_lessons' => $this->lessonRepo->count(),
            'total_topics' => $this->topicRepo->count(),
            'total_instructors' => $this->instructorRepo->count()
        ];
    }

    /**
     * Determina si un archivo es de video
     */
    private function isVideoFile(string $filePath): bool
    {
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv', 'webm'];
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        return in_array($extension, $videoExtensions);
    }
    
    /**
     * Registra un archivo adjunto
     */
    private function registerAttachment(string $filePath, int $sectionId, string $filename): void
    {
        try {
            $fileSize = filesize($filePath);
            $fileType = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
            $mimeType = $this->attachmentRepo->getMimeType($filePath);
            
            $this->attachmentRepo->create($sectionId, $filename, $filePath, $fileSize, $fileType, $mimeType);
        } catch (Exception $e) {
            error_log("Error registrando archivo adjunto: {$filePath} - " . $e->getMessage());
        }
    }
    
    /**
     * Sincroniza la metadata de una lección desde el archivo JSON
     */
    private function syncLessonMetadata(string $filePath, int $lessonId): void
    {
        try {
            // Verificar si existe archivo de metadata
            $metadataPath = $this->metadataManager->getMetadataPath($filePath);
            
            if (file_exists($metadataPath)) {
                error_log("🔄 Sincronizando metadata para lección {$lessonId} desde: {$metadataPath}");
                
                // Sincronizar con la base de datos
                $this->metadataManager->syncWithDatabase($filePath, $lessonId, $this->noteRepo, $this->commentRepo);
                
                error_log("✅ Metadata sincronizada para lección {$lessonId}");
            } else {
                error_log("ℹ️ No hay metadata para sincronizar en lección {$lessonId}");
            }
        } catch (Exception $e) {
            error_log("❌ Error sincronizando metadata para lección {$lessonId}: " . $e->getMessage());
        }
    }
}
