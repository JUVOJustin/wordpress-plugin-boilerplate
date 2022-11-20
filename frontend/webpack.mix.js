let mix = require('laravel-mix');
require('laravel-mix-purgecss');
require('laravel-mix-merge-manifest');

mix.js('frontend/js/demo-plugin-public.js', 'js')
   .sass('frontend/scss/demo-plugin-public.scss', 'css')
   .purgeCss({
      content: ['frontend/views/**/*.twig'],
      css: ['frontend/dist/**/*.css']
  })
  .setPublicPath('frontend/dist')
  .setResourceRoot('../')
  .mergeManifest();
