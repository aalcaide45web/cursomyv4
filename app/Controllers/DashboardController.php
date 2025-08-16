<?php declare(strict_types=1);

// Incluir dependencias en orden correcto
require_once __DIR__ . '/../Services/DB.php';
require_once __DIR__ . '/../Repositories/BaseRepository.php';
require_once __DIR__ . '/../Repositories/TopicRepository.php';
require_once __DIR__ . '/../Repositories/InstructorRepository.php';
require_once __DIR__ . '/../Repositories/CourseRepository.php';
require_once __DIR__ . '/../Repositories/SectionRepository.php';
require_once __DIR__ . '/../Repositories/LessonRepository.php';
require_once __DIR__ . '/../Lib/JsonResponse.php';

class DashboardController
{
    private TopicRepository $topicRepo;
    private InstructorRepository $instructorRepo;
    private CourseRepository $courseRepo;
    private SectionRepository $sectionRepo;
    private LessonRepository $lessonRepo;
    
    public function __construct()
    {
        $this->topicRepo = new TopicRepository();
        $this->instructorRepo = new InstructorRepository();
        $this->courseRepo = new CourseRepository();
        $this->sectionRepo = new SectionRepository();
        $this->lessonRepo = new LessonRepository();
    }
    
    public function index(): void
    {
        $title = 'Dashboard - CursoMy LMS Lite';
        $content = file_get_contents(__DIR__ . '/../Views/pages/dashboard.php');
        
        include __DIR__ . '/../Views/partials/layout.php';
    }
    
    public function getStats(): void
    {
        try {
            $stats = [
                'total_courses' => $this->courseRepo->count(),
                'total_lessons' => $this->lessonRepo->count(),
                'total_topics' => $this->topicRepo->count(),
                'total_instructors' => $this->instructorRepo->count()
            ];
            
            JsonResponse::ok($stats);
        } catch (Exception $e) {
            JsonResponse::error('Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
    
    public function getCourses(): void
    {
        try {
            $courses = $this->courseRepo->getAllWithDetails();
            
            // Formatear los cursos para el frontend
            $formattedCourses = array_map(function($course) {
                return [
                    'id' => $course['id'],
                    'name' => $course['name'],
                    'slug' => $course['slug'],
                    'topic_name' => $course['topic_name'],
                    'topic_slug' => $course['topic_slug'],
                    'instructor_name' => $course['instructor_name'],
                    'instructor_slug' => $course['instructor_slug'],
                    'cover_path' => $course['cover_path'],
                    'avg_rating' => (float) $course['avg_rating'],
                    'ratings_count' => (int) $course['ratings_count']
                ];
            }, $courses);
            
            JsonResponse::ok([
                'courses' => $formattedCourses,
                'count' => count($formattedCourses)
            ]);
        } catch (Exception $e) {
            JsonResponse::error('Error al obtener cursos: ' . $e->getMessage());
        }
    }
}
