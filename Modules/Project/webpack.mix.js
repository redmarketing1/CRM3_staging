const mix = require('laravel-mix');

mix.js(`${__dirname}/Resources/assets/js/project.index.js`, `${__dirname}/Assets/js/product.index.js`)
    .css(`${__dirname}/Resources/assets/css/project.index.css`, `${__dirname}/Assets/js/project.index.css`);