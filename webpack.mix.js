let mix = require('laravel-mix');
require('laravel-mix-purgecss');

 mix.js('frontend/js/demo-plugin-public.js', 'frontend/dist/js')
     .sass('frontend/scss/demo-plugin-public.scss', 'frontend/dist/css')

mix.js('admin/js/demo-plugin.js', 'admin/dist/js')
    .sass('admin/scss/demo-plugin.scss', 'admin/dist/css')
