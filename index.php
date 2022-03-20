<?php
/**
 * Plugin Name: Gift Test
 * Plugin URI: https://github.com/alexhor/GiftTest
 * Description: Online Gift Test
 * Version: 1.0.0
 * Author: Alexander Hornig
 * Author uri: https://h-software.de
 * License: GPLv3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: gifttest
 * Domain Path: /lang
 *
 * @package gifttest
 */

/*
 * Copyright 2020 Alexander Hornig
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */
namespace Gift_Test;

use Gift_Test\Questionaire\Questionaire;

// Load giftest
define( 'ABSPATH',  __DIR__ );
require_once ABSPATH . '/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-plugin-loader.php';
$loader = new Plugin_Loader();

// Show gifttest
do_action('init');
do_action('wp_enqueue_scripts');
require_once plugin_dir_path( __FILE__ ) . 'parts/header.php';

// Get all questionaires
$questionaireList = Settings_Page::get_questionaire_list();

// If only one questionaire exists, show it straight away
if ( 1 == count( $questionaireList ) ) {
	echo Shortcode::render_shortcode( [ 'test-id' => $questionaireList[0]->get_id() ] );
}
// Check if a valid questionaire was selected
else if ( isset( $_REQUEST['test-id'] ) && false !== Questionaire::get( $_REQUEST['test-id'] ) ) {
	echo Shortcode::render_shortcode( [ 'test-id' => $_REQUEST['test-id'] ] );
}
// Show questionaire selection
else {
	foreach ( $questionaireList as $questionaire ) {
		echo '<a href="?test-id=' . $questionaire->get_id() . '">' . $questionaire->get_name() . '</a><br>';
	}
}

require_once plugin_dir_path( __FILE__ ) . 'parts/footer.php';
