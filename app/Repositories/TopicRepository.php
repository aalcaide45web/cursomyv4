<?php declare(strict_types=1);

class TopicRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        return parent::findById($id, 'topic');
    }
    
    public function findBySlug(string $slug): ?array
    {
        return $this->findOneBy('topic', 'slug', $slug);
    }
    
    public function findAll(): array
    {
        return parent::findAll('topic', 'name ASC');
    }
    
    public function create(string $name, string $slug): int
    {
        return $this->insert('topic', [
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    public function update(int $id, string $name, string $slug): bool
    {
        return $this->update('topic', $id, [
            'name' => $name,
            'slug' => $slug
        ]);
    }
    
    public function delete(int $id): bool
    {
        return parent::delete('topic', $id);
    }
    
    public function count(): int
    {
        return parent::count('topic');
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
