<?php declare(strict_types=1);

class LessonRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM lesson WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findBySection(int $sectionId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM lesson WHERE section_id = ? ORDER BY order_index ASC");
        $stmt->execute([$sectionId]);
        
        return $stmt->fetchAll();
    }
    
    public function findByCourse(int $courseId): array
    {
        $sql = "SELECT l.* FROM lesson l
                JOIN section s ON l.section_id = s.id
                WHERE s.course_id = ?
                ORDER BY s.order_index ASC, l.order_index ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll();
    }
    
    public function findByFilePath(string $filePath): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM lesson WHERE file_path = ? LIMIT 1");
        $stmt->execute([$filePath]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function create(int $sectionId, string $name, string $filePath, int $orderIndex = 0): int
    {
        $columns = 'section_id, name, file_path, duration_seconds, thumb_path, order_index';
        $placeholders = ':section_id, :name, :file_path, :duration_seconds, :thumb_path, :order_index';
        
        $stmt = $this->db->prepare("INSERT INTO lesson ({$columns}) VALUES ({$placeholders})");
        $stmt->execute([
            'section_id' => $sectionId,
            'name' => $name,
            'file_path' => $filePath,
            'duration_seconds' => 0,
            'thumb_path' => null,
            'order_index' => $orderIndex
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'duration_seconds', 'thumb_path', 'order_index']), ARRAY_FILTER_USE_KEY);
        
        if (empty($updateData)) {
            return false;
        }
        
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($updateData)));
        $stmt = $this->db->prepare("UPDATE lesson SET {$setClause} WHERE id = :id");
        $updateData['id'] = $id;
        
        return $stmt->execute($updateData);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM lesson WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function countBySection(int $sectionId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lesson WHERE section_id = ?");
        $stmt->execute([$sectionId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function countByCourse(int $courseId): int
    {
        $sql = "SELECT COUNT(*) FROM lesson l
                JOIN section s ON l.section_id = s.id
                WHERE s.course_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM lesson");
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
    }
    
    public function updateDuration(int $id, float $durationSeconds): bool
    {
        $stmt = $this->db->prepare("UPDATE lesson SET duration_seconds = :duration_seconds WHERE id = :id");
        return $stmt->execute(['id' => $id, 'duration_seconds' => $durationSeconds]);
    }
    
    public function updateThumbnail(int $id, string $thumbPath): bool
    {
        $stmt = $this->db->prepare("UPDATE lesson SET thumb_path = :thumb_path WHERE id = :id");
        return $stmt->execute(['id' => $id, 'thumb_path' => $thumbPath]);
    }
    
    public function reorder(int $sectionId, array $lessonIds): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($lessonIds as $orderIndex => $lessonId) {
                $this->update($lessonId, ['order_index' => $orderIndex]);
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }
}
