<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

# TODO: move to config file
define( 'HASH_SALT', '$CAil<,U=?Px4i%{9TQ +_Oo$f!3Cv>XPW5&`UptY4D-#6^9jsa4r-vjg~!?do1F' );

/**
 * Appends a trailing slash.
 *
 * Will remove trailing forward and backslashes if it exists already before adding
 * a trailing forward slash. This prevents double slashing a string or path.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 1.2.0
 *
 * @param string $string What to add the trailing slash to.
 * @return string String with trailing slash added.
 */
function trailingslashit( string $string ) {
	return untrailingslashit( $string ) . '/';
}

/**
 * Removes trailing forward slashes and backslashes if they exist.
 *
 * The primary use of this is for paths and thus should be used for paths. It is
 * not restricted to paths and offers no specific path support.
 *
 * @since 2.2.0
 *
 * @param string $string What to remove the trailing slashes from.
 * @return string String without the trailing slashes.
 */
function untrailingslashit( string $string ) {
	return rtrim( $string, '/\\' );
}

/**
 * Get the filesystem directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the filesystem path of the directory that contains the plugin.
 */
function plugin_dir_path( string $file ) {
  return trailingslashit( dirname( $file )  );
}

/**
 * Get the URL directory path (with trailing slash) for the plugin __FILE__ passed in.
 *
 * @since 2.8.0
 *
 * @param string $file The filename of the plugin (__FILE__).
 * @return string the URL path of the directory that contains the plugin.
 */
function plugin_dir_url( string $file ) {
  # TODO: make non root urls possible
  return trailingslashit( dirname( getRelativePath( __FILE__, $file ) ) );
}

/**
 * Get relative path
 * function from https://stackoverflow.com/a/2642303
 */
 function getRelativePath($from, $to)
 {
    $from = explode('/', $from);
    $to = explode('/', $to);
    foreach($from as $depth => $dir)
    {

         if(isset($to[$depth]))
         {
             if($dir === $to[$depth])
             {
                unset($to[$depth]);
                unset($from[$depth]);
             }
             else
             {
                break;
             }
         }
     }
     //$rawresult = implode('/', $to);
     for($i=0;$i<count($from)-1;$i++)
     {
         array_unshift($to,'..');
     }
     $result = implode('/', $to);
     return $result;
 }

$actionList = [];
/**
 * Add a new action
 *
 * @param string   $hook_name       The name of the action to add the callback to.
 * @param callable $callback        The callback to be run when the action is called.
 * @param int      $priority        Optional. Used to specify the order in which the functions
 *                                  associated with a particular action are executed.
 *                                  Lower numbers correspond with earlier execution,
 *                                  and functions with the same priority are executed
 *                                  in the order in which they were added to the action. Default 10.
 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
 * @return true Always returns true.
 */
function add_action( string $hook_name, callable $callback, int $priority = 10, int $accepted_args = 1 ) {
  global $actionList;
  // Make sure the hook exists
	if ( !isset( $actionList[ $hook_name ] ) ) {
    $actionList[ $hook_name ] = [];
  }
  // Make sure the priority exists
  if ( !isset( $actionList[ $hook_name ][ $priority ] ) ) {
    $actionList[ $hook_name ][ $priority ] = [];
  }
  // Add the callback
  $actionList[ $hook_name ][ $priority ][] = [
    'callback' => $callback,
    'accepted_args' => $accepted_args,
  ];
}

/**
 * Run all callbacks for an action
 *
 * @param string $hook_name   The name of the action to call.
 * @param array  $args        Parameters to pass to the callback functions.
 */
function do_action( string $hook_name, array $args=[] ) {
  global $actionList;
  // Do nothing if no callback functions are defined
  if ( !isset( $actionList[ $hook_name ] ) ) {
    return;
  }

  $num_args = count( $args );
  // Run callbacks ordered by priority
  foreach ( $actionList[ $hook_name ] as $priority => $callbackList ) {
    // Run all callbacks for this priority
    foreach ( $callbackList as $callbackData ) {
      if ( 0 == $callbackData['accepted_args'] ) {
				$value = call_user_func( $callbackData['callback'] );
			}
      else if ( $callbackData['accepted_args'] >= $num_args ) {
				$value = call_user_func_array( $callbackData['callback'], $args );
			}
      else {
				$value = call_user_func_array( $callbackData['callback'], array_slice( $args, 0, (int) $callbackData['accepted_args'] ) );
			}
    }
  }
}

/**
 * @param int $magic
 * @return string|false
 */
function get_byteorder( $magic ) {
	// The magic is 0x950412de.

	// bug in PHP 5.0.2, see https://savannah.nongnu.org/bugs/?func=detailitem&item_id=10565
	$magic_little    = (int) - 1794895138;
	$magic_little_64 = (int) 2500072158;
	// 0xde120495
	$magic_big = ( (int) - 569244523 ) & 0xFFFFFFFF;
	if ( $magic_little == $magic || $magic_little_64 == $magic ) {
		return 'little';
	} elseif ( $magic_big == $magic ) {
		return 'big';
	} else {
		return false;
	}
}

$translationList = [];
/**
 * Load the plugins textdomain
 */
function load_plugin_textdomain( string $domain, bool $deprecated=false, string $plugin_dir='' ) {
  global $translationList;
  $mofile = $domain . '-' . determine_locale() . '.mo';
  $file = fopen( trailingslashit( $plugin_dir ) . $mofile, 'rb' );

  // Read endian
  $endian        = 'little';
  $bytes         = fread( $file, 4 );
  $endian_letter = ( 'big' === $endian ) ? 'N' : 'V';
  $int           = unpack( $endian_letter, $bytes );
  $endian_string = get_byteorder( reset( $int ) );

  // Read header
  $endian = ( 'big' === $endian_string ) ? 'N' : 'V';
  $header = fread( $file, 24 );
	$header = unpack( "{$endian}revision/{$endian}total/{$endian}originals_lengths_addr/{$endian}translations_lengths_addr/{$endian}hash_length/{$endian}hash_addr", $header );
	if ( ! is_array( $header ) ) {
		return false;
	}

  // Seek to beginning of translations
  fseek( $file, $header['originals_lengths_addr'], SEEK_SET);

  // Read originals' indices.
	$originals_lengths_length = $header['translations_lengths_addr'] - $header['originals_lengths_addr'];
	if ( $originals_lengths_length != $header['total'] * 8 ) {
		return false;
	}

	$originals = fread( $file, $originals_lengths_length );
	if ( strlen( $originals ) != $originals_lengths_length ) {
		return false;
	}

	// Read translations' indices.
	$translations_lengths_length = $header['hash_addr'] - $header['translations_lengths_addr'];
	if ( $translations_lengths_length != $header['total'] * 8 ) {
		return false;
	}

	$translations = fread( $file, $translations_lengths_length );
	if ( strlen( $translations ) != $translations_lengths_length ) {
		return false;
	}

	// Transform raw data into set of indices.
	$originals    = str_split( $originals, 8 );
	$translations = str_split( $translations, 8 );

	// Skip hash table.
	$strings_addr = $header['hash_addr'] + $header['hash_length'] * 4;
	fseek( $file, $strings_addr, SEEK_SET );

  // Read all
  $bytes = 4096;
  $strings = '';
	while ( ! feof( $file ) ) {
    $strings .= fread( $file, $bytes);
  }
  fclose( $file );

  // Construct entries
  for ( $i = 0; $i < $header['total']; $i++ ) {
		$o = unpack( "{$endian}length/{$endian}pos", $originals[ $i ] );
		$t = unpack( "{$endian}length/{$endian}pos", $translations[ $i ] );
		if ( ! $o || ! $t ) {
      echo 'HERE';
			return false;
		}

		// Adjust offset due to reading strings to separate space before.
		$o['pos'] -= $strings_addr;
		$t['pos'] -= $strings_addr;

		$original    = substr( $strings, $o['pos'], $o['length'] );
		$translation = substr( $strings, $t['pos'], $t['length'] );

    // Skip headers
		if ( '' !== $original ) {
			$translationList[ $original ] = $translation;
		}
	}

  return true;
}

/**
 * Determine the current locale
 */
function determine_locale() {
  # TODO: implement
  return 'de_DE';
}

/**
 * Translation functions
 */
function __( string $text, string $domain ) {
	global $translationList;
	if ( isset( $translationList[ $text ] ) ) {
		return $translationList[ $text ];
	}
	else {
		return $text;
	}
}
function _e( string $text, string $domain ) { echo __( $text, $domain ); }
function esc_html__( string $text, string $domain ) { return __( $text, $domain ); }
function esc_html_e( string $text, string $domain ) { _e( $text, $domain ); }

/**
 * Check if this user is an admin
 */
function is_admin() {
  # TODO: implement
  return true;
}

function current_user_can() {
	# TODO: implement
	return true;
}

$shortcodeList = [];
/**
 * Register a shortcode
 */
function add_shortcode( string $tag, callable $callback ) {
  global $shortcodeList;
  $shortcodeList[ $tag ] = $callback;
}

$scriptFileList = [];
/**
 * Register javascript files
 */
function wp_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
	global $scriptFileList;
	$scriptFileList[ $handle ] = [
		'src' => $src,
		'version' => $ver,
		'in_footer' => $in_footer,
		'deps' => $deps,
		'data' => '',
	];
}
function enqueue_script( $script ) {
	echo '<script>' . $script[ 'data' ] . '</script>';
	echo '<script src="' . $script[ 'src' ] . '">' . $script[ 'data' ] . '</script>';
}

$styleFileList = [];
/**
 * Register style files
 */
function wp_register_style( $handle, $src, $deps = array(), $ver = false, $in_footer = false, $media = 'all' ) {
	global $styleFileList;
	$scriptFileList[ $handle ] = [
		'src' => $src,
		'version' => $ver,
		'in_footer' => $in_footer,
		'deps' => $deps,
		'data' => '',
	];
}
function enqueue_style( $style ) {
	echo '<link rel="stylesheet" href="' . $style[ 'src' ] . '">';
}

/**
 * Localize a script.
 *
 * Works only if the script has already been registered.
 *
 * Accepts an associative array $l10n and creates a JavaScript object:
 *
 *     "$object_name" = {
 *         key: value,
 *         key: value,
 *         ...
 *     }
 *
 * @see WP_Scripts::localize()
 * @link https://core.trac.wordpress.org/ticket/11520
 * @global WP_Scripts $wp_scripts The WP_Scripts object for printing scripts.
 *
 * @since 2.2.0
 *
 * @todo Documentation cleanup
 *
 * @param string $handle      Script handle the data will be attached to.
 * @param string $object_name Name for the JavaScript object. Passed directly, so it should be qualified JS variable.
 *                            Example: '/[a-zA-Z0-9_]+/'.
 * @param array  $l10n        The data itself. The data can be either a single or multi-dimensional array.
 * @return bool True if the script was successfully localized, false otherwise.
 */
function wp_localize_script( string $handle, string $object_name, array $l10n ) {
	global $scriptFileList;
	// Make sure the file exists
	if( !isset( $scriptFileList[ $handle ] ) ) {
		return;
	}

	# html entitiy decode
	if ( is_string( $l10n ) ) {
		$l10n = html_entity_decode( $l10n, ENT_QUOTES, 'UTF-8' );
	} else {
		foreach ( (array) $l10n as $key => $value ) {
			if ( ! is_scalar( $value ) ) {
				continue;
			}

			$l10n[ $key ] = html_entity_decode( (string) $value, ENT_QUOTES, 'UTF-8' );
		}
	}

	# build script snippet
	$scriptFileList[ $handle ][ 'data' ] .= "var $object_name = " . json_encode( $l10n ) . ';';
}

/**
 * Creates a cryptographic token tied to a specific action, user, user session,
 * and window of time.
 *
 * @since 2.0.3
 * @since 4.0.0 Session tokens were integrated with nonce creation
 *
 * @param string|int $action Scalar value to add context to the nonce.
 * @return string The token.
 */
function wp_create_nonce( $action = -1 ) {
	$token = wp_get_session_token();
	$i     = wp_nonce_tick();

	return substr( hash_hmac( 'md5', $i . '|' . $action . '|' . $token, HASH_SALT), -12, 10 );
}

/**
 * Returns the time-dependent variable for nonce creation.
 *
 * A nonce has a lifespan of two ticks. Nonces in their second tick may be
 * updated, e.g. by autosave.
 *
 * @since 2.5.0
 *
 * @return float Float value rounded up to the next highest integer.
 */
function wp_nonce_tick() {
	/**
	 * Filters the lifespan of nonces in seconds.
	 *
	 * @since 2.5.0
	 *
	 * @param int $lifespan Lifespan of nonces in seconds. Default 86,400 seconds, or one day.
	 */
	$nonce_life = 86400;

	return ceil( time() / ( $nonce_life / 2 ) );
}

/**
 * Retrieve the current session token from the logged_in cookie.
 *
 * @since 4.0.0
 *
 * @return string Token.
 */
function wp_get_session_token() {
	return ! empty( $_COOKIE['token'] ) ? $_COOKIE['token'] : '';
}

/**
 * Verifies the Ajax request to prevent processing requests external of the blog.
 *
 * @since 2.0.3
 *
 * @param int|string   $action    Action nonce.
 * @param false|string $query_arg Optional. Key to check for the nonce in `$_REQUEST` (since 2.5). If false,
 *                                `$_REQUEST` values will be evaluated for '_ajax_nonce', and '_wpnonce'
 *                                (in that order). Default false.
 * @param bool         $die       Optional. Whether to die early when the nonce cannot be verified.
 *                                Default true.
 * @return int|false 1 if the nonce is valid and generated between 0-12 hours ago,
 *                   2 if the nonce is valid and generated between 12-24 hours ago.
 *                   False if the nonce is invalid.
 */
function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) {
	$nonce = '';

	if ( $query_arg && isset( $_REQUEST[ $query_arg ] ) ) {
		$nonce = $_REQUEST[ $query_arg ];
	} elseif ( isset( $_REQUEST['_ajax_nonce'] ) ) {
		$nonce = $_REQUEST['_ajax_nonce'];
	} elseif ( isset( $_REQUEST['_wpnonce'] ) ) {
		$nonce = $_REQUEST['_wpnonce'];
	}

	return wp_verify_nonce( $nonce, $action );
}

/**
 * Verifies that a correct security nonce was used with time limit.
 *
 * A nonce is valid for 24 hours (by default).
 *
 * @since 2.0.3
 *
 * @param string     $nonce  Nonce value that was used for verification, usually via a form field.
 * @param string|int $action Should give context to what is taking place and be the same when nonce was created.
 * @return int|false 1 if the nonce is valid and generated between 0-12 hours ago,
 *                   2 if the nonce is valid and generated between 12-24 hours ago.
 *                   False if the nonce is invalid.
 */
function wp_verify_nonce( $nonce, $action = -1 ) {
	$nonce = (string) $nonce;
	if ( empty( $nonce ) ) {
		return false;
	}

	$token = wp_get_session_token();
	$i     = wp_nonce_tick();

	// Nonce generated 0-12 hours ago.
	#$expected = substr( wp_hash( $i . '|' . $action . '|' . $token, 'nonce' ), -12, 10 );
	$expected = substr( hash_hmac( 'md5', $i . '|' . $action . '|' . $token, HASH_SALT), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 1;
	}

	// Nonce generated 12-24 hours ago.
	#$expected = substr( wp_hash( ( $i - 1 ) . '|' . $action . '|' . $uid . '|' . $token, 'nonce' ), -12, 10 );
	$expected = substr( hash_hmac( 'md5', $i - 1 . '|' . $action . '|' . $token, HASH_SALT), -12, 10 );
	if ( hash_equals( $expected, $nonce ) ) {
		return 2;
	}

	// Invalid nonce.
	return false;
}

/**
 * Kill an ajax request
 */
function wp_die( string $message ) {
	die( $message );
}

$newDb = mysqli_init();
# TODO: get from config file
$db = new \mysqli('gabentest-h-software-de_db:3306', 'php', 'EucNHe8YJek1qyf9IVYVhvLOXsLBxbSmIGEx0N6RBXqTCfhYu7qzf2s8B0o4VPQa0RyMIh2OsnfI4nnh4oCY2YbjVG8B66l1pEmzQip8ManBEHNiRqmF6IKJJQsEnaedYHm2eh3YKmddleQ1GyTfx4y9b4oZPjSWqqf5ywV6XhfJoL8jg736VFFlTvzEsytmSVPLLVmscpepQfkwRwfv0zYh7TGJStk4hVPQjFnwSIRDdkkELQ8PIVVtv8VGeIQ', 'php');
/**
 * Get an option from the database
 */
function get_option( string $option, $default = false ) {
	global $db;
	$request = $db->prepare("SELECT data FROM options WHERE name = ?");
	$request->bind_param('s', $option);
	$request->execute();
	$result = $request->get_result();

	// Catch invalid results
	if( 1 !== $result->num_rows ) {
		return $default;
	}
	$request->close();
	$rawResult = $result->fetch_assoc();
	return unserialize( trim( $rawResult[ 'data' ] ) );
}

/**
 * Updates the value of an option that was already added.
 *
 * You do not need to serialize values. If the value needs to be serialized,
 * then it will be serialized before it is inserted into the database.
 * Remember, resources cannot be serialized or added as an option.
 *
 * If the option does not exist, it will be created.
 * This function is designed to work with or without a logged-in user. In terms of security,
 * plugin developers should check the current user's capabilities before updating any options.
 *
 * @since 1.0.0
 * @since 4.2.0 The `$autoload` parameter was added.
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string      $option   Name of the option to update. Expected to not be SQL-escaped.
 * @param mixed       $value    Option value. Must be serializable if non-scalar. Expected to not be SQL-escaped.
 * @param string|bool $autoload Optional. Whether to load the option when WordPress starts up. For existing options,
 *                              `$autoload` can only be updated using `update_option()` if `$value` is also changed.
 *                              Accepts 'yes'|true to enable or 'no'|false to disable. For non-existent options,
 *                              the default value is 'yes'. Default null.
 * @return bool True if the value was updated, false otherwise.
 */
function update_option( string $option, mixed $value, string|bool $autoload = null ) {
	global $db;
	// Check the option already exists
	$request = $db->prepare("SELECT name FROM options WHERE name = ?");
	$request->bind_param('s', $option);
	$request->execute();
	$result = $request->get_result();
	$request->close();

	// Add an new option if none exists
	if( 0 === $result->num_rows ) {
		return add_option( $option, $value, '', $autoload );
	}

	$request = $db->prepare("UPDATE options SET data = ? WHERE name = ?");
	$serializedValue = serialize( $value );
	$request->bind_param('ss', $serializedValue, $option );
	$request->execute();
	$request->close();
	return true;
}

/**
 * Adds a new option.
 *
 * You do not need to serialize values. If the value needs to be serialized,
 * then it will be serialized before it is inserted into the database.
 * Remember, resources cannot be serialized or added as an option.
 *
 * You can create options without values and then update the values later.
 * Existing options will not be updated and checks are performed to ensure that you
 * aren't adding a protected WordPress option. Care should be taken to not name
 * options the same as the ones which are protected.
 *
 * @since 1.0.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string      $option     Name of the option to add. Expected to not be SQL-escaped.
 * @param mixed       $value      Optional. Option value. Must be serializable if non-scalar.
 *                                Expected to not be SQL-escaped.
 * @param string      $deprecated Optional. Description. Not used anymore.
 * @param string|bool $autoload   Optional. Whether to load the option when WordPress starts up.
 *                                Default is enabled. Accepts 'no' to disable for legacy reasons.
 * @return bool True if the option was added, false otherwise.
 */
function add_option( string $option, mixed $value = '', string $deprecated = '', string|bool $autoload = 'yes' ) {
	global $db;
	// Check the option doesn't already exists
	$request = $db->prepare("SELECT name FROM options WHERE name = ?");
	$request->bind_param('s', $option);
	$request->execute();
	$result = $request->get_result();
	$request->close();

	// Only add a new option if it doesn't exist yet
	if( 0 !== $result->num_rows ) {
		return false;
	}

	$request = $db->prepare("INSERT INTO options ( name, data ) VALUES ( ?, ? )");
	$serializedValue = serialize( $value );
	$request->bind_param('ss', $option, $serializedValue );
	$request->execute();
	$request->close();
	return true;
}

/**
 * Removes option by name. Prevents removal of protected WordPress options.
 *
 * @since 1.2.0
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param string $option Name of the option to delete. Expected to not be SQL-escaped.
 * @return bool True if the option was deleted, false otherwise.
 */
function delete_option( string $option ) {
	global $db;
	$request = $db->prepare("DELTE FROM options WHERE name = ?");
	$request->bind_param('s', $option);
	$request->execute();
	$request->close();
	return true;
}

/**
 * Send a JSON response back to an Ajax request.
 *
 * @since 3.5.0
 * @since 4.7.0 The `$status_code` parameter was added.
 * @since 5.6.0 The `$options` parameter was added.
 *
 * @param mixed $response    Variable (usually an array or object) to encode as JSON,
 *                           then print and die.
 * @param int   $status_code Optional. The HTTP status code to output. Default null.
 * @param int   $options     Optional. Options to be passed to json_encode(). Default 0.
 */
function wp_send_json( mixed $response, int $status_code = null, int $options = 0 ) {
	header( 'Content-Type: application/json; charset=utf-8' );
	echo json_encode( $response );
	die();
}


/**
 * Sanitizes a string from user input or from the database.
 *
 * - Checks for invalid UTF-8,
 * - Converts single `<` characters to entities
 * - Strips all tags
 * - Removes line breaks, tabs, and extra whitespace
 * - Strips octets
 *
 * @since 2.9.0
 *
 * @see sanitize_textarea_field()
 * @see wp_check_invalid_utf8()
 * @see wp_strip_all_tags()
 *
 * @param string $str String to sanitize.
 * @return string Sanitized string.
 */
function sanitize_text_field( string $str ) {
	$keep_newlines = true;
	if ( is_object( $str ) || is_array( $str ) ) {
		return '';
	}

	$str = (string) $str;

	$filtered = wp_check_invalid_utf8( $str );

	if ( strpos( $filtered, '<' ) !== false ) {
		$filtered = wp_pre_kses_less_than( $filtered );
		// This will strip extra whitespace for us.
		$filtered = wp_strip_all_tags( $filtered, false );

		// Use HTML entities in a special case to make sure no later
		// newline stripping stage could lead to a functional tag.
		$filtered = str_replace( "<\n", "&lt;\n", $filtered );
	}

	if ( ! $keep_newlines ) {
		$filtered = preg_replace( '/[\r\n\t ]+/', ' ', $filtered );
	}
	$filtered = trim( $filtered );

	$found = false;
	while ( preg_match( '/%[a-f0-9]{2}/i', $filtered, $match ) ) {
		$filtered = str_replace( $match[0], '', $filtered );
		$found    = true;
	}

	if ( $found ) {
		// Strip out the whitespace that may now exist after removing the octets.
		$filtered = trim( preg_replace( '/ +/', ' ', $filtered ) );
	}

	return $filtered;
}

/**
 * Checks for invalid UTF8 in a string.
 *
 * @since 2.8.0
 *
 * @param string $string The text which is to be checked.
 * @param bool   $strip  Optional. Whether to attempt to strip out invalid UTF8. Default false.
 * @return string The checked text.
 */
function wp_check_invalid_utf8( $string, $strip = false ) {
	$string = (string) $string;

	if ( 0 === strlen( $string ) ) {
		return '';
	}

	// Store the site charset as a static to avoid multiple calls to get_option().
	static $is_utf8 = null;
	if ( ! isset( $is_utf8 ) ) {
		$is_utf8 = in_array( get_option( 'blog_charset' ), array( 'utf8', 'utf-8', 'UTF8', 'UTF-8' ), true );
	}
	if ( ! $is_utf8 ) {
		return $string;
	}

	// Check for support for utf8 in the installed PCRE library once and store the result in a static.
	static $utf8_pcre = null;
	if ( ! isset( $utf8_pcre ) ) {
		// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$utf8_pcre = @preg_match( '/^./u', 'a' );
	}
	// We can't demand utf8 in the PCRE installation, so just return the string in those cases.
	if ( ! $utf8_pcre ) {
		return $string;
	}

	// phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged -- preg_match fails when it encounters invalid UTF8 in $string.
	if ( 1 === @preg_match( '/^./us', $string ) ) {
		return $string;
	}

	// Attempt to strip the bad chars if requested (not recommended).
	if ( $strip && function_exists( 'iconv' ) ) {
		return iconv( 'utf-8', 'utf-8', $string );
	}

	return '';
}

/**
 * Convert lone less than signs.
 *
 * KSES already converts lone greater than signs.
 *
 * @since 2.3.0
 *
 * @param string $text Text to be converted.
 * @return string Converted text.
 */
function wp_pre_kses_less_than( $text ) {
	return preg_replace_callback( '%<[^>]*?((?=<)|>|$)%', 'wp_pre_kses_less_than_callback', $text );
}

/**
 * Callback function used by preg_replace.
 *
 * @since 2.3.0
 *
 * @param string[] $matches Populated by matches to preg_replace.
 * @return string The text returned after esc_html if needed.
 */
function wp_pre_kses_less_than_callback( $matches ) {
	if ( false === strpos( $matches[0], '>' ) ) {
		return esc_html( $matches[0] );
	}
	return $matches[0];
}

/**
 * Properly strip all HTML tags including script and style
 *
 * This differs from strip_tags() because it removes the contents of
 * the `<script>` and `<style>` tags. E.g. `strip_tags( '<script>something</script>' )`
 * will return 'something'. wp_strip_all_tags will return ''
 *
 * @since 2.9.0
 *
 * @param string $string        String containing HTML tags
 * @param bool   $remove_breaks Optional. Whether to remove left over line breaks and white space chars
 * @return string The processed string.
 */
function wp_strip_all_tags( $string, $remove_breaks = false ) {
	$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
	$string = strip_tags( $string );

	if ( $remove_breaks ) {
		$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
	}

	return trim( $string );
}

/**
 * Removes slashes from a string
 */
function wp_unslash( string $value ) {
	return stripslashes( $value );
}

/**
 * Merges user defined arguments into defaults array.
 *
 * This function is used throughout WordPress to allow for both string or array
 * to be merged into another array.
 *
 * @since 2.2.0
 * @since 2.3.0 `$args` can now also be an object.
 *
 * @param string|array|object $args     Value to merge with $defaults.
 * @param array               $defaults Optional. Array that serves as the defaults.
 *                                      Default empty array.
 * @return array Merged user defined values with defaults.
 */
function wp_parse_args( $args, $defaults = array() ) {
	if ( is_object( $args ) ) {
		$parsed_args = get_object_vars( $args );
	} elseif ( is_array( $args ) ) {
		$parsed_args =& $args;
	} else {
		parse_str( $args, $parsed_args );
	}

	if ( is_array( $defaults ) && $defaults ) {
		return array_merge( $defaults, $parsed_args );
	}
	return $parsed_args;
}
