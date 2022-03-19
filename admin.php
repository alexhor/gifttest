<?php
define( 'ABSPATH',  __DIR__ );

// Load giftest
define( 'ABSPATH',  __DIR__ );
require_once ABSPATH . '/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-plugin-loader.php';
$loader = new Plugin_Loader();

// Show gifttest
do_action('init');
do_action('admin_enqueue_scripts');
