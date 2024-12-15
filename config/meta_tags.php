<?php

use Butschster\Head\MetaTags\Viewport;

return [
    /*
     * Meta title section
     */
    'title'       => [
        'default'    => env('APP_NAME', 'NEU WEST'),
        'separator'  => '|',
        'max_length' => 255,
    ],


    /*
     * Meta description section
     */
    'description' => [
        'default'    => 'NEU WEST BAUUNTERNEHMEN',
        'max_length' => 255,
    ],


    /*
     * Meta keywords section
     */
    'keywords'    => [
        'default'    => 'NEU WEST, BAUUNTERNEHMEN',
        'max_length' => 255,
    ],

    /**
     * Default CSS Style
     */
    'css'         => [
        [
            'url'  => '/assets/fonts/tabler-icons.min.css',
            'name' => 'Tabler Icons',
            // 'attribues' => ['media' => 'custom', 'defer', 'async'],
        ],
        [
            'url'  => '/assets/fonts/feather.css',
            'name' => 'Feather Icons',
        ],
        [
            'url'  => '/assets/fonts/fontawesome.css',
            'name' => 'Font Awesome',
        ],
        [
            'url'  => '/assets/fonts/material.css',
            'name' => 'Material Icons',
        ],
        [
            'url'  => '/assets/css/plugins/style.css',
            'name' => 'Plugin Styles',
        ],
        [
            'url'  => '/assets/css/plugins/bootstrap-switch-button.min.css',
            'name' => 'Bootstrap Switch Button',
        ],
        [
            'url'  => '/assets/css/plugins/datepicker-bs5.min.css',
            'name' => 'Datepicker BS5',
        ],
        [
            'url'  => '/assets/css/plugins/flatpickr.min.css',
            'name' => 'Flatpickr',
        ],
        [
            'url'  => '/assets/css/customizer.css',
            'name' => 'Customizer',
        ],
        [
            'url'  => '/css/custome.css',
            'name' => 'Custom Styles',
        ],
        [
            'url'  => '/css/custom-color.css',
            'name' => 'Custom Color',
        ],
        [
            'url'  => '/assets/css/plugins/dropzone.min.css',
            'name' => 'Dropzone',
        ],
        [
            'url'  => '/assets/css/plugins/select2.min.css',
            'name' => 'Select2',
        ],
        [
            'url'  => '/assets/css/plugins/datatable/dataTables.dataTables.css',
            'name' => 'DataTables',
        ],
        [
            'url'  => '/css/responsive.css',
            'name' => 'Responsive Styles',
        ],
        [
            'url'  => 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css',
            'name' => 'Font Awesome',
        ],
        [
            'url'  => 'https://unpkg.com/nprogress@0.2.0/nprogress.css',
            'name' => 'Nprogress',
        ],
        [
            'url'  => 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css',
            'name' => 'Lightbox',
        ],
        [
            'url'  => 'css/common.css',
            'name' => 'common.css',
        ],
        [
            'url'  => 'css/custome-main.css',
            'name' => 'custome-main',
        ],
    ],



    /**
     * Default JS Script
     */
    'js'          => [
        [
            'url'       => 'https://code.jquery.com/jquery-3.5.1.min.js',
            'name'      => 'jquery',
            'placement' => 'head',
        ],
        [
            'url'       => 'https://unpkg.com/nprogress@0.2.0/nprogress.js',
            'name'      => 'Nprogress Js',
            'placement' => 'head',
        ],
        // [
        //     'url'       => 'https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.14.3/cdn.min.js',
        //     'name'      => 'Alpine Js',
        //     'placement' => 'head',
        //     'attribues' => ['defer'],
        // ],
        // Foter script 
        [
            'url'  => '/js/jquery.validate.min.js',
            'name' => 'jquery.validate',
        ],
        [
            'url'  => '/js/jquery.form.js',
            'name' => 'jquery.validate',
        ],
        [
            'url'  => '/assets/js/dash.js',
            'name' => 'dash.js',
        ],
        [
            'url'  => '/assets/js/plugins/select2.min.js',
            'name' => 'select2',
        ],
        [
            'url'  => '/assets/js/plugins/datatable/dataTables.js',
            'name' => 'dataTables',
        ],
        [
            'url'  => '/assets/js/plugins/datatable/intl.js',
            'name' => 'dataTables intl',
        ],
        [
            'url'  => '/Modules/Taskly/Resources/assets/js/dropzone.min.js',
            'name' => 'dropzone',
        ],
        [
            'url'  => '/js/letter.avatar.js',
            'name' => 'avatar',
        ],
        [
            'url'  => '/assets/js/plugins/apexcharts.min.js',
            'name' => 'apexcharts',
        ],
        [
            'url'  => '/assets/js/plugins/popper.min.js',
            'name' => 'popper.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/perfect-scrollbar.min.js',
            'name' => 'perfect-scrollbar.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/feather.min.js',
            'name' => 'feather.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/simplebar.min.js',
            'name' => 'simplebar.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/simple-datatables.js',
            'name' => 'simple-datatables.js',
        ],
        [
            'url'  => '/assets/js/plugins/bootstrap-switch-button.min.js',
            'name' => 'bootstrap-switch-button.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/sweetalert2.all.min.js',
            'name' => 'sweetalert2.all.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/datepicker-full.min.js',
            'name' => 'datepicker-full.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/flatpickr.min.js',
            'name' => 'flatpickr.min.js',
        ],
        [
            'url'  => '/assets/js/plugins/choices.min.js',
            'name' => 'choices.min.js',
        ],
        [
            'url'  => '/js/custom.js',
            'name' => 'custom.js',
        ],
        [
            'url'  => '/assets/js/plugins/bootstrap.min.js',
            'name' => 'bootstrap.min.js',
        ],
        [
            'url'  => '/Modules/Taskly/Resources/assets/js/tinymce/tinymce.min.js',
            'name' => 'tinymce.min.js',
        ],
        [
            'url'  => 'https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js',
            'name' => 'lightbox.min',
        ],
        [
            'url'  => 'https://maps.google.com/maps/api/js?key=AIzaSyBbTqlUNbqPssvetzvRl4n65HB2g_-o9tE&libraries=places',
            'name' => 'maps.google',
        ],
        [
            'url'  => 'assets/js/plugins/signature_pad/signature_pad.min.js',
            'name' => 'signature_pad',
        ],
        [
            'url'       => 'https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.2/axios.min.js',
            'name'      => 'maps.google',
            'attribues' => ["integrity" => "sha512-b94Z6431JyXY14iSXwgzeZurHHRNkLt9d6bAHt7BZT38eqV+GyngIi/tVye4jBKPYQ2lBdRs0glww4fmpuLRwA==", "crossorigin" => "anonymous", "referrerpolicy" => "no-referrer"],
        ],
    ],


    /*
     * Default packages
     *
     * Packages, that should be included everywhere
     */
    'packages'    => [
        // 'jquery', 'bootstrap',
    ],

    'charset'     => 'utf-8',
    'robots'      => null,
    'viewport'    => Viewport::RESPONSIVE,
    'csrf_token'  => true,
];
