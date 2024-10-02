<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Define which assets will be available through the asset manager
    | These assets are registered on the asset manager.
    |--------------------------------------------------------------------------
    */
    'all_assets'      => [
        'project.index.css'                 => ['public' => 'assets/css/project.index.css'],
        'project.index.daterangepicker.css' => ['Cdn' => 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css'],

        'project.index.moment.js'           => ['Cdn' => 'https://cdn.jsdelivr.net/momentjs/latest/moment.min.js'],
        'project.index.daterangepicker.js'  => ['Cdn' => 'https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js'],
        'project.index.js'                  => ['public' => 'assets/js/project.index.js'],
        'project.show.js'                   => ['public' => 'assets/js/project.show.js'],
    ],

    /* 
    |--------------------------------------------------------------------------
    | Define which default assets will always be included in all pages
    | through the asset pipeline.
    |--------------------------------------------------------------------------
    */
    'required_assets' => [],
];
