<?php
namespace Gift_Test;

// Load giftest
define( 'ABSPATH',  __DIR__ );
require_once ABSPATH . '/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-plugin-loader.php';
$loader = new Plugin_Loader();

// Make sure everything is setup
$db->query("CREATE TABLE IF NOT EXISTS options (
  name varchar(256),
  data mediumblob
)");

// Show gifttest settings
do_action('init');
do_action('admin_enqueue_scripts');
require_once plugin_dir_path( __FILE__ ) . 'parts/header.php';
echo '<div id="gifttest"></div>';
require_once plugin_dir_path( __FILE__ ) . 'parts/footer.php';
