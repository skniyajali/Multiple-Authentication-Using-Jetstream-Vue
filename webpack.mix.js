const mix = require("laravel-mix");

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
// require('./resources/js/admin/webpack.mix');

// add this in the root `webpack.mix.js`
// require("./resources/js/admin/webpack.mix.js");

// require("./themes/admin/webpack.mix.js");

mix.js("resources/js/app.js", "public/js")
    .vue()
    .postCss("resources/css/app.css", "public/css", [
        require("postcss-import"),
        require("tailwindcss"),
    ])
    .webpackConfig(require("./webpack.config"));

mix.browserSync("localhost:8000");
if (mix.inProduction()) {
    mix.version();
}
