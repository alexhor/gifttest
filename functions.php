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
	# TODO: finish implementing
	echo '<pre>'; var_export($result);exit;
	$request->close();
	$rawResult = $result->fetch_assoc();
	return unserialize( trim( $rawResult[ 'data' ] ) );
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
