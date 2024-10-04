const mix = require('laravel-mix');
mix
    .js(`${__dirname}/Resources/assets/js/project.index.js`, 'public/assets/js/project.index.js')
    .js(`${__dirname}/Resources/assets/js/project.show.js`, 'public/assets/js/project.show.js')
    .js(`${__dirname}/Resources/assets/js/project.map.js`, 'public/assets/js/project.map.js')
    .sass(`${__dirname}/Resources/assets/css/project.index.scss`, 'public/assets/css/project.index.css');