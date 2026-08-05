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
        $slug = str_ends_with($page, '.html') ? substr($page, 0, -5) : ltrim($page, '/');
        $catalog = \App\Support\MateenPages::catalog();
        if (isset($catalog[$slug])) {
            return url($catalog[$slug]['clean']);
        }

        return url('/'.$slug);
    }
}
