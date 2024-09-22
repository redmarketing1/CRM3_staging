const glob = require("glob");
const mix = require("laravel-mix");
const { execSync } = require('child_process');

// ----------------------------------
// Mix Global (App-Wide) Resource Files
// ----------------------------------

// JS and CSS files located in `resources/`
mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css')
    .version(); // Optional: cache-busting

// ----------------------------------
// Mix Module-Specific Assets Dynamically
// ----------------------------------

// Dynamically load the webpack.mix.js from each module
let configs = glob.sync("./Modules/*/webpack.mix.js");

// If the module is set in the environment variable, load only that module's webpack.mix.js
if (process.env.module !== undefined) {
    let module = process.env.module.charAt(0).toUpperCase() + process.env.module.slice(1);
    configs = [`./Modules/${module}/webpack.mix.js`];
}

// Loop through each module's Mix file and require it
configs.forEach(config => {
    require(config);

    // Extract the module name using regex
    let module = config.match(/Modules\/(\w+?)\//);

    if (module !== null) {
        let moduleName = module[1];

        // Automatically publish the module's assets after compilation
        mix.after(() => {
            execSync(`php artisan module:publish ${moduleName}`);
        });
    }
});

// ----------------------------------
// Additional Options
// ----------------------------------

// Enable source maps (for debugging)
mix.sourceMaps();

// Enable BrowserSync for live reloading
mix.browserSync('localhost:8000'); // Update with your local dev URL
