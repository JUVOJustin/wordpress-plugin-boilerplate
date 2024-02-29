<?php

if (defined('WP_CLI') && WP_CLI) {
	$setup = new Demo_Plugin\Cli\Setup();
	WP_CLI::add_command('setup', $setup);
}

return;