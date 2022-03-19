<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
 * Check if this user is an admin
 */
function is_admin() {
  # TODO: implement
  return false;
}

$shortcodeList = [];
/**
 * Register a shortcode
 */
function add_shortcode( string $tag, callable $callback ) {
  global $shortcodeList;
  $shortcodeList[ $tag ] = $callback;
}

/**
 * Register javascript files
 */
function wp_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false ) {
  echo '<script src="' . $src . '"></script>';
}

$styleFileList = [];
/**
 * Register style files
 */
function wp_register_style( $handle, $src, $deps = array(), $ver = false, $in_footer = false, $media = 'all' ) {
  echo '<link rel="stylesheet" href="' . $src . '">';
}
