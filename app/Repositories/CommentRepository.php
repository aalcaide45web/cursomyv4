<?php declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

class CommentRepository extends BaseRepository
{
    /**
     * Encuentra un comentario por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM comment WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Encuentra todos los comentarios de una lección
     */
    public function findByLesson(int $lessonId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM comment 
            WHERE lesson_id = ? 
            ORDER BY created_at DESC
        ");
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea un nuevo comentario
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO comment (lesson_id, text, t_seconds, created_at) 
            VALUES (?, ?, ?, datetime('now'))
        ");
        $stmt->execute([
            $data['lesson_id'],
            $data['text'],
            $data['t_seconds'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Actualiza un comentario existente
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE comment 
            SET text = ?, t_seconds = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['text'],
            $data['t_seconds'] ?? null,
            $id
        ]);
    }
    
    /**
     * Elimina un comentario
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM comment WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Elimina todos los comentarios de una lección
     */
    public function deleteByLesson(int $lessonId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM comment WHERE lesson_id = ?");
        return $stmt->execute([$lessonId]);
    }
    
    /**
     * Cuenta los comentarios de una lección
     */
    public function countByLesson(int $lessonId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM comment WHERE lesson_id = ?");
        $stmt->execute([$lessonId]);
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Encuentra un comentario por lección y texto
     */
    public function findByLessonAndText(int $lessonId, string $text): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM comment WHERE lesson_id = ? AND text = ? LIMIT 1");
        $stmt->execute([$lessonId, $text]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
