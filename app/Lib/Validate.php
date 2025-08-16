<?php declare(strict_types=1);

class Validate
{
    public static function int($value): ?int
    {
        return is_numeric($value) ? (int) $value : null;
    }
    
    public static function float($value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
    
    public static function string($value, int $maxLength = 1000): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        
        $value = trim($value);
        return strlen($value) <= $maxLength ? $value : null;
    }
}
