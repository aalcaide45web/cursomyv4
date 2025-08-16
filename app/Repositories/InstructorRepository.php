<?php declare(strict_types=1);

class InstructorRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return parent::findById($id, 'instructor');
    }
    
    public function findBySlug(string $slug): ?array
    {
        return $this->findOneBy('instructor', 'slug', $slug);
    }
    
    public function findAll(): array
    {
        return parent::findAll('instructor', 'name ASC');
    }
    
    public function create(string $name, string $slug): int
    {
        return $this->insert('instructor', [
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    public function update(int $id, string $name, string $slug): bool
    {
        return $this->update('instructor', $id, [
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    public function delete(int $id): bool
    {
        return parent::delete('instructor', $id);
    }
    
    public function count(): int
    {
        return parent::count('instructor');
    }
    
    public function findOrCreate(string $name, string $slug): array
    {
        $existing = $this->findBySlug($slug);
        if ($existing) {
            return $existing;
        }
        
        $id = $this->create($name, $slug);
        return $this->findById($id);
    }
}
