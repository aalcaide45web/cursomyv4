<?php declare(strict_types=1);

class SectionRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM section WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findByCourse(int $courseId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM section WHERE course_id = ? ORDER BY order_index ASC");
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll();
    }
    
    public function create(int $courseId, string $name, int $orderIndex = 0): int
    {
        $columns = 'course_id, name, order_index';
        $placeholders = ':course_id, :name, :order_index';
        
        $stmt = $this->db->prepare("INSERT INTO section ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(['course_id' => $courseId, 'name' => $name, 'order_index' => $orderIndex]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'order_index']), ARRAY_FILTER_USE_KEY);
        
        if (empty($updateData)) {
            return false;
        }
        
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($updateData)));
        $stmt = $this->db->prepare("UPDATE section SET {$setClause} WHERE id = :id");
        $updateData['id'] = $id;
        
        return $stmt->execute($updateData);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM section WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function countByCourse(int $courseId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM section WHERE course_id = ?");
        $stmt->execute([$courseId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function reorder(int $courseId, array $sectionIds): bool
    {
        try {
            DB::beginTransaction();
            
            foreach ($sectionIds as $orderIndex => $sectionId) {
                $this->update($sectionId, ['order_index' => $orderIndex]);
            }
            
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollback();
            return false;
        }
    }
}
