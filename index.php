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

// Load giftest
define( 'ABSPATH',  __DIR__ );
require_once ABSPATH . '/functions.php';
require_once plugin_dir_path( __FILE__ ) . 'class/class-plugin-loader.php';
$loader = new Plugin_Loader();

// Show gifttest
do_action('init');
require_once plugin_dir_path( __FILE__ ) . 'parts/header.php';
echo '<div id="gifttest"></div>';
require_once plugin_dir_path( __FILE__ ) . 'parts/footer.php';
