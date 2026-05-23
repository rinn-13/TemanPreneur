<?php

namespace App\Utils;

class ImageUrl
{
    public static function normalize(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        $trimmed = trim($path);
        if ($trimmed === '') {
            return null;
        }

        // If text already contains an absolute URL, return the last one found.
        if (preg_match_all('#https?://[^\s"\']+#i', $trimmed, $matches)) {
            return end($matches[0]);
        }

        if (preg_match('#^/storage/#i', $trimmed)) {
            return asset($trimmed);
        }

        if (preg_match('#^storage/#i', $trimmed)) {
            $relative = preg_replace('#^storage[/\\]+#i', '', $trimmed);
            return asset('storage/' . $relative);
        }

        return asset('storage/' . ltrim($trimmed, '/'));
    }
}
