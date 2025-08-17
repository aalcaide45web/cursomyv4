<?php declare(strict_types=1);

abstract class BaseRepository
{
    protected PDO $db;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
    
    /**
     * Obtiene la conexión PDO
     */
    public function getConnection(): PDO
    {
        return $this->db;
    }
}
