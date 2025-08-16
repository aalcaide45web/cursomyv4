<?php declare(strict_types=1);

class AttachmentRepository extends BaseRepository
{
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM attachment WHERE id = ?");
        $stmt->execute([$id]);
        
        $result = $stmt->fetch();
        return $result ?: null;
    }
    
    public function findBySection(int $sectionId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM attachment WHERE section_id = ? ORDER BY filename ASC");
        $stmt->execute([$sectionId]);
        
        return $stmt->fetchAll();
    }
    
    public function findByCourse(int $courseId): array
    {
        $sql = "SELECT a.* FROM attachment a
                JOIN section s ON a.section_id = s.id
                WHERE s.course_id = ?
                ORDER BY s.order_index ASC, a.filename ASC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return $stmt->fetchAll();
    }
    
    public function create(int $sectionId, string $filename, string $filePath, int $fileSize, string $fileType, ?string $mimeType = null): int
    {
        $columns = 'section_id, filename, file_path, file_size, file_type, mime_type';
        $placeholders = ':section_id, :filename, :file_path, :file_size, :file_type, :mime_type';
        
        $stmt = $this->db->prepare("INSERT INTO attachment ({$columns}) VALUES ({$placeholders})");
        $stmt->execute([
            'section_id' => $sectionId,
            'filename' => $filename,
            'file_path' => $filePath,
            'file_size' => $fileSize,
            'file_type' => $fileType,
            'mime_type' => $mimeType
        ]);
        
        return (int) $this->db->lastInsertId();
    }
    
    public function update(int $id, array $data): bool
    {
        $updateData = array_filter($data, fn($key) => in_array($key, ['filename', 'file_path', 'file_size', 'file_type', 'mime_type']), ARRAY_FILTER_USE_KEY);
        
        if (empty($updateData)) {
            return false;
        }
        
        $setClause = implode(', ', array_map(fn($key) => "{$key} = :{$key}", array_keys($updateData)));
        $stmt = $this->db->prepare("UPDATE attachment SET {$setClause} WHERE id = :id");
        $updateData['id'] = $id;
        
        return $stmt->execute($updateData);
    }
    
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM attachment WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public function deleteBySection(int $sectionId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM attachment WHERE section_id = ?");
        return $stmt->execute([$sectionId]);
    }
    
    public function countBySection(int $sectionId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM attachment WHERE section_id = ?");
        $stmt->execute([$sectionId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    public function countByCourse(int $courseId): int
    {
        $sql = "SELECT COUNT(*) FROM attachment a
                JOIN section s ON a.section_id = s.id
                WHERE s.course_id = ?";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$courseId]);
        
        return (int) $stmt->fetchColumn();
    }
    
    /**
     * Obtiene el MIME type de un archivo
     */
    public function getMimeType(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'xml' => 'application/xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        
        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }
}
