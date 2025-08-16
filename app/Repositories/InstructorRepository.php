<?php declare(strict_types=1);

class InstructorRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM instructor WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM instructor WHERE slug = ? LIMIT 1");
        $stmt->execute([$slug]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findAll(): array
    {
        $stmt = $this->db->prepare("SELECT * FROM instructor ORDER BY name ASC");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    public function create(string $name, string $slug): int
    {
        $columns = 'name, slug';
        $placeholders = ':name, :slug';
        
        $stmt = $this->db->prepare("INSERT INTO instructor ({$columns}) VALUES ({$placeholders})");
        $stmt->execute(['name' => $name, 'slug' => $slug]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, string $name, string $slug): bool
    {
        $stmt = $this->db->prepare("UPDATE instructor SET name = :name, slug = :slug WHERE id = :id");
        return $stmt->execute(['id' => $id, 'name' => $name, 'slug' => $slug]);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM instructor WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function count(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM instructor");
        $stmt->execute();
        
        return (int) $stmt->fetchColumn();
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
