<?php
/**
 * Cleanup all options on plugin uninstall
 *
 * @package gifttest
 */

namespace Gift_Test;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class/class-settings-page.php';


$settings_page = new Settings_Page();
$success       = true;
foreach ( $settings_page->get_questionaire_list() as $questionaire ) {
	$success = $success && $questionaire->delete();
}
return $success;
