> You most likely want to overwrite this readme with your own content

# What is this boilerplate
This boilerplate is a fork of [WordPress Boilerplate](https://github.com/DevinVinson/WordPress-Plugin-Boilerplate) but with namespaces, shortcodes support, phpstan and laravel mix support out of the box. 

## How to use

 1. Create a folder inside your wordpress installation and name it after your plugin
 2. Copy a dump of this repo inside the folder
 3. Make sure `setup.sh` is executable (`chmod +x setup.sh`)
 4. Run `setup sh` . The `-f` parameter determines the filename (*snake_case*) for assets while the `-n` parameter sets the namespace and php class filenames (*Pascal_Snake_Case*). If your plugins name should be 'Awesome Plugin' the full command would look like: `./setup.sh -f awesome_plugin -n Awesome_Plugin`. 
 5. That's it. You maybe want to remove `setup.sh` or this `readme.md` file

## How to use [SatisPress](https://github.com/cedaro/satispress) for testing premium dependencies like ACF Pro
To install third party plugins SatisPress is used. It allows to host plugins as composer packages. by default these dependencies should be available at plugins.juvo-design.de/satispress. The repo is already added to the composer.json inside tests/setup. There you can add all available packages. To run install inside the subdirectory easily use `npm run composer:test`. During the install you will be asked for credentials. Please do not commit any `auth.json`. The github actions are setup to use a `SATISPRESS_KEY` secret.
