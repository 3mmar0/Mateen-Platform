<?php

namespace App\Support;

class MateenAsset
{
    public static function url(string $path = ''): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        if (str_starts_with($path, 'Mateen/')) {
            return asset($path);
        }

        return asset('Mateen/'.($path === '' ? '' : $path));
    }
}
