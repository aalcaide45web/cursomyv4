<?php declare(strict_types=1);

class Str
{
    public static function slugify(string $text): string
    {
        // TODO: Implementar slugify en FASE 1
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text), '-'));
    }
}
