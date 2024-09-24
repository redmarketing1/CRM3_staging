const mix = require('laravel-mix');

mix.js(`${__dirname}/Resources/assets/js/project.index.js`, `/assets/js/project.index.js`)
    .sass(`${__dirname}/Resources/assets/css/project.index.scss`, `/assets/css/project.index.css`);