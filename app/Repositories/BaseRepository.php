<?php declare(strict_types=1);

abstract class BaseRepository
{
    protected PDO $db;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    protected function findById(int $id, string $table): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    protected function findAll(string $table, string $orderBy = 'id ASC'): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} ORDER BY {$orderBy}");
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
    
    protected function findBy(string $table, string $column, $value, string $orderBy = 'id ASC'): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$column} = ? ORDER BY {$orderBy}");
        $stmt->execute([$value]);
        
        return $stmt->fetchAll();
    }
    
    protected function findOneBy(string $table, string $column, $value): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$table} WHERE {$column} = ? LIMIT 1");
        $stmt->execute([$value]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    protected function insert(string $table, array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $stmt = $this->db->prepare("INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})");
        $stmt->execute($data);
        
        return (int) $this->db->lastInsertId();
    }
    
    protected function update(string $table, int $id, array $data): bool
    {
        $setClause = implode(', ', array_map(fn($col) => "{$col} = :{$col}", array_keys($data)));
        
        $stmt = $this->db->prepare("UPDATE {$table} SET {$setClause} WHERE id = :id");
        $data['id'] = $id;
        
        return $stmt->execute($data);
    }
    
    protected function delete(string $table, int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$table} WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    protected function count(string $table, string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        return (int) $stmt->fetchColumn();
    }
}
