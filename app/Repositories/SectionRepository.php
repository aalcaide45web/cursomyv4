<?php declare(strict_types=1);

class SectionRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return parent::findById($id, 'section');
    }
    
    public function findByCourse(int $courseId): array
    {
        return $this->findBy('section', 'course_id', $courseId, 'order_index ASC');
    }
    
    public function create(int $courseId, string $name, int $orderIndex = 0): int
    {
        return $this->insert('section', [
            'course_id' => $courseId,
            'name' => $name,
            'order_index' => $orderIndex
        ]);
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['name', 'order_index']), ARRAY_FILTER_USE_KEY);
        return $this->update('section', $id, $updateData);
    }
    
    public function delete(int $id): bool
    {
        return parent::delete('section', $id);
    }
    
    public function countByCourse(int $courseId): int
    {
        return parent::count('section', 'course_id = ?', [$courseId]);
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
