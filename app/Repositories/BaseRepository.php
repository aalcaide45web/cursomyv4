<?php declare(strict_types=1);

abstract class BaseRepository
{
    protected PDO $db;
    
    public function __construct()
    {
        $this->db = DB::getInstance();
    }
}
