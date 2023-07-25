const mix = require('laravel-mix');

// const productResourcePath = 'Modules/Product/resources';

mix.css('resources/css/app.css', 'public/css/app.css');
// mix.css(productResourcePath + '/css/product.css', 'public/css/product.css')

// Compile JS files
// mix.js(productResourcePath + '/js/product.js', 'public/js/product.js');
mix.js('resources/js/app.js', 'public/js/app.js');