<?php declare(strict_types=1);

class LessonRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return parent::findById($id, 'lesson');
    }
    
    public function findBySection(int $sectionId): array
    {
        return $this->findBy('lesson', 'section_id', $sectionId, 'order_index ASC');
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
        return $this->findOneBy('lesson', 'file_path', $filePath);
    }
    
    public function create(int $sectionId, string $name, string $filePath, int $orderIndex = 0): int
    {
        return $this->insert('lesson', [
            'section_id' => $sectionId,
            'name' => $name,
            'file_path' => $filePath,
            'duration_seconds' => 0,
            'thumb_path' => null,
            'order_index' => $orderIndex
        ]);
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'duration_seconds', 'thumb_path', 'order_index']), ARRAY_FILTER_USE_KEY);
        return $this->update('lesson', $id, $updateData);
    }
    
    public function delete(int $id): bool
    {
        return parent::delete('lesson', $id);
    }
    
    public function countBySection(int $sectionId): int
    {
        return parent::count('lesson', 'section_id = ?', [$sectionId]);
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
    
    public function updateDuration(int $id, float $durationSeconds): bool
    {
        return $this->update($id, ['duration_seconds' => $durationSeconds]);
    }
    
    public function updateThumbnail(int $id, string $thumbPath): bool
    {
        return $this->update($id, ['thumb_path' => $thumbPath]);
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
