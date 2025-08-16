<?php declare(strict_types=1);

class Time
{
    public static function now(): string
    {
        return date('Y-m-d H:i:s');
    }
    
    public static function formatSeconds(float $seconds): string
    {
        // TODO: Implementar formato de tiempo en FASE 1
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = floor($seconds % 60);
        
        if ($hours > 0) {
            return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
        }
        return sprintf('%02d:%02d', $minutes, $secs);
    }
}
