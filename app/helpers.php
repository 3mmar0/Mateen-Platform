<?php

use App\Support\MateenAsset;

if (! function_exists('mateen_asset')) {
    function mateen_asset(string $path = ''): string
    {
        return MateenAsset::url($path);
    }
}

if (! function_exists('mateen_page')) {
    function mateen_page(string $page): string
    {
        return url('/Mateen/html/'.$page.(str_ends_with($page, '.html') ? '' : '.html'));
    }
}
