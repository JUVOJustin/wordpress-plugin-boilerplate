const mix = require('laravel-mix');
require('laravel-mix-purgecss');
require('laravel-mix-merge-manifest');

mix.js('admin/js/demo-plugin.js', 'js')
    .sass('admin/scss/demo-plugin.scss', 'css')
    // .purgeCss({
    //     content: ['frontend/views/**/*.twig']
    // })
    .minify(['admin/dist/js/demo-plugin.js', 'admin/dist/css/demo-plugin.css'])
    .setPublicPath('admin/dist')
    .mergeManifest();