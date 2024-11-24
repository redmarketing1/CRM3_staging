const glob = require('glob');
const mix = require('laravel-mix');
const { execSync } = require('child_process');

// mix.js('resources/js/app.js', 'public/assets/js/app.js')
//     // .sass('resources/sass/app.scss', 'public/assets/css')
//     .version();

let configs = glob.sync("./Modules/*/webpack.mix.js");
configs.forEach(config => {
    let moduleName = config.match(/Modules[\\/](\w+?)[\\/]/)[1];
    let modulePath = (`${__dirname}/${config}`);

    if (modulePath !== null) {
        require(modulePath);
        mix.after(() => {
            // execSync(`php artisan module:publish ${moduleName}`);
        });
    }
});
