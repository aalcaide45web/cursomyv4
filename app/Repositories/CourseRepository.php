<?php declare(strict_types=1);

class CourseRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return parent::findById($id, 'course');
    }
    
    public function findBySlug(string $slug): ?array
    {
        return $this->findOneBy('course', 'slug', $slug);
    }
    
    public function findAllActive(): array
    {
        return $this->findBy('course', 'is_deleted', 0, 'name ASC');
    }
    
    public function findByTopic(int $topicId): array
    {
        return $this->findBy('course', 'topic_id', $topicId, 'name ASC');
    }
    
    public function findByInstructor(int $instructorId): array
    {
        return $this->findBy('course', 'instructor_id', $instructorId, 'name ASC');
    }
    
    public function create(array $data): int
    {
        return $this->insert('course', [
            'topic_id' => $data['topic_id'],
            'instructor_id' => $data['instructor_id'],
            'name' => $data['name'],
            'slug' => $data['slug'],
            'cover_path' => $data['cover_path'] ?? null,
            'avg_rating' => 0,
            'ratings_count' => 0,
            'is_deleted' => 0
        ]);
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'slug', 'cover_path']), ARRAY_FILTER_USE_KEY);
        return $this->update('course', $id, $updateData);
    }
    
    public function softDelete(int $id): bool
    {
        return $this->update('course', $id, ['is_deleted' => 1]);
    }
    
    public function reactivate(int $id): bool
    {
        return $this->update('course', $id, ['is_deleted' => 0]);
    }
    
    public function updateRating(int $id, float $avgRating, int $ratingsCount): bool
    {
        return $this->update('course', $id, [
            'avg_rating' => $avgRating,
            'ratings_count' => $ratingsCount
        ]);
    }
    
    public function count(): int
    {
        return parent::count('course', 'is_deleted = 0');
    }
    
    public function countDeleted(): int
    {
        return parent::count('course', 'is_deleted = 1');
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
