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

        'project.google.map.js'             => ['Cdn' => 'https://maps.googleapis.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&d?.js'],

        'project.map.css'                   => ['public' => 'assets/css/project.maps.css'],
        'project.map.js'                    => ['public' => 'assets/js/project.map.js'],
    ],

    /* 
    |--------------------------------------------------------------------------
    | Define which default assets will always be included in all pages
    | through the asset pipeline.
    |--------------------------------------------------------------------------
    */
    'required_assets' => [],
];
