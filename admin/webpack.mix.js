let mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js('admin/js/demo-plugin.js', 'admin/dist/js')
    .sass('admin/scss/demo-plugin.scss', 'admin/dist/css')
    .mergeManifest();
