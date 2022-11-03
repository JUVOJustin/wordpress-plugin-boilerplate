<?php

/*
Plugin Name: Integration Tests Setup
Plugin Author: JUVOJustin
Description: Set up WordPress for Integration Tests.
*/

class Setup
{

    public function __construct() {

    }

}

// Execute function after plugin init
add_action('plugins_loaded', function() {

});
