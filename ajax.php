<?php
namespace Gift_Test;

// Load giftest
define( 'ABSPATH',  __DIR__ );
require_once ABSPATH . '/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-plugin-loader.php';
$loader = new Plugin_Loader();
do_action('init');

// Only process valid ajax requests
if ( isset( $_REQUEST['_ajax_nonce'], $_REQUEST['action'] ) ) {
  do_action( 'wp_ajax_' . $_REQUEST['action'] );
}
// Catch invalid requests
http_response_code(400);
