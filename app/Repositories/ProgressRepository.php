<?php declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

class ProgressRepository extends BaseRepository
{
    /**
     * Encuentra el progreso de una lección
     */
    public function findByLesson(int $lessonId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM progress 
            WHERE lesson_id = ? 
            ORDER BY last_seen_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$lessonId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Encuentra el progreso de un usuario en un curso
     */
    public function findByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, l.name as lesson_name, s.name as section_name
            FROM progress p
            JOIN lesson l ON p.lesson_id = l.id
            JOIN section s ON l.section_id = s.id
            WHERE s.course_id = ?
            ORDER BY p.last_seen_at DESC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Guarda o actualiza el progreso de una lección
     */
    public function saveProgress(int $lessonId, float $position): bool
    {
        // Verificar si ya existe un registro de progreso
        $existing = $this->findByLesson($lessonId);
        
        if ($existing) {
            // Actualizar progreso existente
            $stmt = $this->db->prepare("
                UPDATE progress 
                SET last_t_seconds = ?, 
                    last_seen_at = datetime('now'),
                    total_watched_seconds = total_watched_seconds + ?
                WHERE lesson_id = ?
            ");
            return $stmt->execute([$position, $position, $lessonId]);
        } else {
            // Crear nuevo registro de progreso
            $stmt = $this->db->prepare("
                INSERT INTO progress (
                    lesson_id, 
                    last_t_seconds, 
                    total_watched_seconds, 
                    last_seen_at, 
                    created_at
                ) VALUES (?, ?, ?, datetime('now'), datetime('now'))
            ");
            return $stmt->execute([$lessonId, $position, $position]);
        }
    }
    
    /**
     * Marca una lección como completada
     */
    public function markAsCompleted(int $lessonId): bool
    {
        $stmt = $this->db->prepare("
            UPDATE progress 
            SET is_completed = 1, 
                completed_at = datetime('now'),
                last_seen_at = datetime('now')
            WHERE lesson_id = ?
        ");
        return $stmt->execute([$lessonId]);
    }
    
    /**
     * Obtiene estadísticas de progreso de un curso
     */
    public function getCourseProgress(int $courseId): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                COUNT(DISTINCT l.id) as total_lessons,
                COUNT(DISTINCT p.lesson_id) as lessons_with_progress,
                COUNT(DISTINCT CASE WHEN p.is_completed = 1 THEN p.lesson_id END) as completed_lessons,
                AVG(p.last_t_seconds) as avg_position,
                SUM(p.total_watched_seconds) as total_watched_time
            FROM lesson l
            JOIN section s ON l.section_id = s.id
            LEFT JOIN progress p ON l.id = p.lesson_id
            WHERE s.course_id = ?
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    /**
     * Elimina el progreso de una lección
     */
    public function deleteByLesson(int $lessonId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM progress WHERE lesson_id = ?");
        return $stmt->execute([$lessonId]);
    }
    
    /**
     * Elimina todo el progreso de un curso
     */
    public function deleteByCourse(int $courseId): bool
    {
        $stmt = $this->db->prepare("
            DELETE FROM progress 
            WHERE lesson_id IN (
                SELECT l.id FROM lesson l 
                JOIN section s ON l.section_id = s.id 
                WHERE s.course_id = ?
            )
        ");
        return $stmt->execute([$courseId]);
    }
}
