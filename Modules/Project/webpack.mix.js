const mix = require('laravel-mix');

mix.js(`${__dirname}/Resources/assets/js/project.index.js`, `/assets/js/project.index.js`)
    .css(`${__dirname}/Resources/assets/css/project.index.css`, `/assets/css/project.index.css`);