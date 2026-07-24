<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Panel PWA (tenant tarafi)
    |--------------------------------------------------------------------------
    */

    'theme_color' => '#604ae3',
    'background_color' => '#ffffff',

    /** Service worker onbellek surumu — asset guncellemesinde artirin. */
    'cache_version' => '1',

    'icons' => [
        ['src' => '/apple-touch-icon.png', 'sizes' => '180x180', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => '/apple-touch-icon.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => '/apple-touch-icon.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
        ['src' => '/apple-touch-icon.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
    ],

];
