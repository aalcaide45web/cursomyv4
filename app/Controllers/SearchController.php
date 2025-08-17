<?php declare(strict_types=1);

require_once __DIR__ . '/../Services/DB.php';
require_once __DIR__ . '/../Repositories/BaseRepository.php';
require_once __DIR__ . '/../Repositories/CourseRepository.php';
require_once __DIR__ . '/../Repositories/LessonRepository.php';
require_once __DIR__ . '/../Repositories/TopicRepository.php';
require_once __DIR__ . '/../Repositories/InstructorRepository.php';
require_once __DIR__ . '/../Lib/JsonResponse.php';

class SearchController
{
    private CourseRepository $courseRepo;
    private LessonRepository $lessonRepo;
    private TopicRepository $topicRepo;
    private InstructorRepository $instructorRepo;
    
    public function __construct()
    {
        $this->courseRepo = new CourseRepository();
        $this->lessonRepo = new LessonRepository();
        $this->topicRepo = new TopicRepository();
        $this->instructorRepo = new InstructorRepository();
    }
    
    /**
     * Búsqueda global en tiempo real
     */
    public function search(): void
    {
        try {
            $query = $_GET['q'] ?? '';
            $limit = min((int)($_GET['limit'] ?? 20), 50); // Máximo 50 resultados
            
            if (empty($query)) {
                JsonResponse::ok(['results' => [], 'count' => 0, 'query' => '']);
                return;
            }
            
            $results = $this->performSearch($query, $limit);
            
            JsonResponse::ok([
                'results' => $results,
                'count' => count($results),
                'query' => $query
            ]);
            
        } catch (Exception $e) {
            JsonResponse::error('Error en la búsqueda: ' . $e->getMessage());
        }
    }
    
    /**
     * Realiza la búsqueda en múltiples entidades
     */
    private function performSearch(string $query, int $limit): array
    {
        $results = [];
        
        // Buscar en cursos
        $courses = $this->searchCourses($query, $limit);
        foreach ($courses as $course) {
            $results[] = [
                'type' => 'course',
                'id' => $course['id'],
                'title' => $course['name'],
                'subtitle' => "Curso por {$course['instructor_name']}",
                'topic' => $course['topic_name'],
                'url' => "/course/{$course['slug']}",
                'score' => $this->calculateRelevance($query, $course['name'])
            ];
        }
        
        // Buscar en lecciones
        $lessons = $this->searchLessons($query, $limit);
        foreach ($lessons as $lesson) {
            $results[] = [
                'type' => 'lesson',
                'id' => $lesson['id'],
                'title' => $lesson['name'],
                'subtitle' => "Lección del curso {$lesson['course_name']}",
                'topic' => $lesson['topic_name'],
                'url' => "/lesson/{$lesson['id']}",
                'score' => $this->calculateRelevance($query, $lesson['name'])
            ];
        }
        
        // Buscar en topics
        $topics = $this->searchTopics($query, $limit);
        foreach ($topics as $topic) {
            $results[] = [
                'type' => 'topic',
                'id' => $topic['id'],
                'title' => $topic['name'],
                'subtitle' => "Temática",
                'topic' => $topic['name'],
                'url' => "/topic/{$topic['slug']}",
                'score' => $this->calculateRelevance($query, $topic['name'])
            ];
        }
        
        // Buscar en instructores
        $instructors = $this->searchInstructors($query, $limit);
        foreach ($instructors as $instructor) {
            $results[] = [
                'type' => 'instructor',
                'id' => $instructor['id'],
                'title' => $instructor['name'],
                'subtitle' => "Instructor",
                'topic' => '',
                'url' => "/instructor/{$instructor['slug']}",
                'score' => $this->calculateRelevance($query, $instructor['name'])
            ];
        }
        
        // Ordenar por relevancia y limitar resultados
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($results, 0, $limit);
    }
    
    private function searchCourses(string $query, int $limit): array
    {
        $sql = "SELECT c.*, t.name as topic_name, i.name as instructor_name 
                FROM course c 
                JOIN topic t ON c.topic_id = t.id 
                JOIN instructor i ON c.instructor_id = i.id 
                WHERE c.is_deleted = 0 
                AND (c.name LIKE :query OR t.name LIKE :query OR i.name LIKE :query)
                ORDER BY c.name ASC 
                LIMIT :limit";
        
        $stmt = $this->courseRepo->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function searchLessons(string $query, int $limit): array
    {
        $sql = "SELECT l.*, s.name as section_name, c.name as course_name, t.name as topic_name
                FROM lesson l 
                JOIN section s ON l.section_id = s.id 
                JOIN course c ON s.course_id = c.id 
                JOIN topic t ON c.topic_id = t.id 
                WHERE c.is_deleted = 0 
                AND l.name LIKE :query 
                ORDER BY l.name ASC 
                LIMIT :limit";
        
        $stmt = $this->lessonRepo->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function searchTopics(string $query, int $limit): array
    {
        $sql = "SELECT * FROM topic WHERE name LIKE :query ORDER BY name ASC LIMIT :limit";
        $stmt = $this->topicRepo->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function searchInstructors(string $query, int $limit): array
    {
        $sql = "SELECT * FROM instructor WHERE name LIKE :query ORDER BY name ASC LIMIT :limit";
        $stmt = $this->instructorRepo->getConnection()->prepare($sql);
        $stmt->bindValue(':query', "%{$query}%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    private function calculateRelevance(string $query, string $text): float
    {
        $query = strtolower($query);
        $text = strtolower($text);
        
        // Coincidencia exacta al inicio
        if (strpos($text, $query) === 0) {
            return 100.0;
        }
        
        // Coincidencia en cualquier parte
        if (strpos($text, $query) !== false) {
            return 80.0;
        }
        
        // Coincidencia parcial de palabras
        $queryWords = explode(' ', $query);
        $textWords = explode(' ', $text);
        $matches = 0;
        
        foreach ($queryWords as $word) {
            if (strlen($word) < 3) continue;
            foreach ($textWords as $textWord) {
                if (strpos($textWord, $word) !== false) {
                    $matches++;
                    break;
                }
            }
        }
        
        if ($matches > 0) {
            return 60.0 * ($matches / count($queryWords));
        }
        
        return 0.0;
    }
}
