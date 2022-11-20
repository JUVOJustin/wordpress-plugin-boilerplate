let mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js('admin/js/demo-plugin.js', 'js')
    .sass('admin/scss/demo-plugin.scss', 'css')
    .setPublicPath('admin/dist')
    .setResourceRoot('../')
    .mergeManifest();
