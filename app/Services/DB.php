<?php declare(strict_types=1);

class DB
{
    private static ?PDO $instance = null;
    private static array $config;
    
    private function __construct() {}
    private function __clone() {}
    
    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::$config = require __DIR__ . '/../../config/env.php';
            self::$instance = self::createConnection();
        }
        
        return self::$instance;
    }
    
    private static function createConnection(): PDO
    {
        $dbPath = self::$config['DB_PATH'];
        
        try {
            $pdo = new PDO("sqlite:$dbPath");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
            // Configuraciones SQLite para mejor rendimiento
            $pdo->exec('PRAGMA journal_mode = WAL');
            $pdo->exec('PRAGMA synchronous = NORMAL');
            $pdo->exec('PRAGMA cache_size = 10000');
            $pdo->exec('PRAGMA temp_store = MEMORY');
            
            return $pdo;
        } catch (PDOException $e) {
            throw new RuntimeException("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }
    
    public static function beginTransaction(): void
    {
        self::getInstance()->beginTransaction();
    }
    
    public static function commit(): void
    {
        self::getInstance()->commit();
    }
    
    public static function rollback(): void
    {
        self::getInstance()->rollback();
    }
    
    public static function inTransaction(): bool
    {
        return self::getInstance()->inTransaction();
    }
}
