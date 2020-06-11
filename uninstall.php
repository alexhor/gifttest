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


$settings_page = new Settings_Page();
$success       = true;
foreach ( $settings_page->get_questionaire_list() as $questionaire ) {
	$success = $success && $questionaire->delete();
}
return $success;
