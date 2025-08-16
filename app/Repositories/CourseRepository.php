<?php declare(strict_types=1);

class CourseRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findAllActive(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE is_deleted = 0 ORDER BY name ASC");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function findByTopic(int $topicId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE topic_id = ? ORDER BY name ASC");
        $stmt->execute([$topicId]);
        
        return $stmt->fetchAll();
    }
    
    public function findByInstructor(int $instructorId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM course WHERE instructor_id = ? ORDER BY name ASC");
        $stmt->execute([$instructorId]);
        
        return $stmt->fetchAll();
    }
    
    public function create(array $data): int
    {
        $columns = 'topic_id, instructor_id, name, slug, cover_path, avg_rating, ratings_count, is_deleted';
        $placeholders = ':topic_id, :instructor_id, :name, :slug, :cover_path, :avg_rating, :ratings_count, :is_deleted';
        
        $stmt = $this->db->prepare("INSERT INTO course ({$columns}) VALUES ({$placeholders})");
        $stmt->execute([
            'topic_id' => $data['topic_id'],
            'instructor_id' => $data['instructor_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'cover_path' => $data['cover_path'] ?? null,
            'avg_rating' => 0,
            'ratings_count' => 0,
            'is_deleted' => 0
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'slug', 'cover_path']), ARRAY_FILTER_USE_KEY);
        
        if (empty($updateData)) {
            return false;
        }
        
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($updateData)));
        $stmt = $this->db->prepare("UPDATE course SET {$setClause} WHERE id = :id");
        $updateData['id'] = $id;
        
        return $stmt->execute($updateData);
    }
    
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE course SET is_deleted = 1 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function reactivate(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE course SET is_deleted = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function updateRating(int $id, float $avgRating, int $ratingsCount): bool
    {
        $stmt = $this->db->prepare("UPDATE course SET avg_rating = :avg_rating, ratings_count = :ratings_count WHERE id = :id");
        return $stmt->execute(['id' => $id, 'avg_rating' => $avgRating, 'ratings_count' => $ratingsCount]);
    }
    
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM course WHERE is_deleted = 0");
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    public function countDeleted(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM course WHERE is_deleted = 1");
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    public function getWithDetails(int $id): ?array
    {
        $sql = "SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                       i.name as instructor_name, i.slug as instructor_slug
                FROM course c
                JOIN topic t ON c.topic_id = t.id
                JOIN instructor i ON c.instructor_id = i.id
                WHERE c.id = ? AND c.is_deleted = 0";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function getAllWithDetails(): array
    {
        $sql = "SELECT c.*, t.name as topic_name, t.slug as topic_slug, 
                       i.name as instructor_name, i.slug as instructor_slug
                FROM course c
                JOIN topic t ON c.topic_id = t.id
                JOIN instructor i ON c.instructor_id = i.id
                WHERE c.is_deleted = 0
                ORDER BY c.name ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
