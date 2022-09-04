let mix = require('laravel-mix');
require('laravel-mix-purgecss');
require('laravel-mix-merge-manifest');

mix.js('frontend/js/demo-plugin-public.js', 'frontend/dist/js')
   .sass('frontend/scss/demo-plugin-public.scss', 'frontend/dist/css')
   .purgeCss({
      content: ['frontend/views/**/*.twig'],
      css: ['frontend/dist/**/*.css']
  })
  .mergeManifest();
