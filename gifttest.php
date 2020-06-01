<?php

/**
 * Plugin Name: Gift Test
 * Plugin URI: https://github.com/alexhor/GiftTest
 * Description: Online Gift Test
 * Version: 0.0.1
 * Author: Alexander Hornig
 * Author uri: https://h-software.de
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: gifttest
 * Domain Path: /lang
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

namespace GiftTest;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'class/PluginLoader.php' );
$loader = new PluginLoader();
