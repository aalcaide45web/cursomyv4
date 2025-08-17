<?php declare(strict_types=1);

require_once __DIR__ . '/BaseRepository.php';

class NoteRepository extends BaseRepository
{
    /**
     * Encuentra una nota por ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM note WHERE id = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
    
    /**
     * Encuentra todas las notas de una lección
     */
    public function findByLesson(int $lessonId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM note 
            WHERE lesson_id = ? 
            ORDER BY t_seconds ASC
        ");
        $stmt->execute([$lessonId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Crea una nueva nota
     */
    public function create(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO note (lesson_id, t_seconds, text, created_at) 
            VALUES (?, ?, ?, datetime('now'))
        ");
        $stmt->execute([
            $data['lesson_id'],
            $data['t_seconds'],
            $data['text']
        ]);
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Actualiza una nota existente
     */
    public function update(int $id, array $data): bool
    {
        $stmt = $this->db->prepare("
            UPDATE note 
            SET t_seconds = ?, text = ?, updated_at = datetime('now')
            WHERE id = ?
        ");
        return $stmt->execute([
            $data['t_seconds'],
            $data['text'],
            $id
        ]);
    }
    
    /**
     * Elimina una nota
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM note WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * Elimina todas las notas de una lección
     */
    public function deleteByLesson(int $lessonId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM note WHERE lesson_id = ?");
        return $stmt->execute([$lessonId]);
    }
    
    /**
     * Cuenta las notas de una lección
     */
    public function countByLesson(int $lessonId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM note WHERE lesson_id = ?");
        $stmt->execute([$lessonId]);
        return (int) $stmt->fetchColumn();
    }
}
