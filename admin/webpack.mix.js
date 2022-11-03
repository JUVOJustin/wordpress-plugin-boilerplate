let mix = require('laravel-mix');
require('laravel-mix-merge-manifest');

mix.js('admin/js/catalyst-portal.js', 'admin/dist/js')
    .sass('admin/scss/catalyst-portal.scss', 'admin/dist/css')
    .mergeManifest();
