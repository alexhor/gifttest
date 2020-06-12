<?php
/**
 * File Name: class-shortcode.php
 *
 * @package gifttest
 */

namespace Gift_Test;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Shortcode' ) ) :
	/**
	 * Shortcode handling class
	 *
	 * @since 1.0.0
	 */
	class Shortcode {
		/**
		 * Register the shortcode
		 *
		 * @since 1.0.0
		 */
		public static function register() {
			// Register the shortcode.
			add_shortcode( 'gifttest', array( self::class, 'render_shortcode' ) );
		}

		/**
		 * Render the shortcode
		 *
		 * @param array $attributes The shortcodes parsed attributes.
		 *
		 * @return string The element for vue.js to hook into.
		 *
		 * @since 1.0.0
		 */
		public static function render_shortcode( array $attributes ) {
			$attributes = shortcode_atts( array( 'test-id' => '1' ), $attributes );

			// Get styles and scripts.
			wp_localize_script(
				'gifttest-questionaire',
				'gifttest',
				array(
					'test-id'             => $attributes['test-id'],
					'plugin_dir_url'      => plugin_dir_url( __DIR__ . '..' ),
					'ajaxurl'             => admin_url( 'admin-ajax.php' ),
					'_ajax_nonce'         => array(
						'get_details' => wp_create_nonce( 'gifttest_get_questionaire_details' ),
					),
					'text'                => array(
						'continue'        => esc_html__( 'Continue', 'gifttest' ),
						'back'            => esc_html__( 'Back', 'gifttest' ),
						'scoreboard'      => esc_html__( 'Scoreboard', 'gifttest' ),
						'print_gift_list' => esc_html__( 'Print gift list', 'gifttest' ),
						'point_singular'  => esc_html__( 'Point', 'gifttest' ),
						'points_plural'   => esc_html__( 'Points', 'gifttest' ),
						'show_more_gifts' => esc_html__( 'Show more gifts', 'gifttest' ),
					),
					'vue_components_path' => plugin_dir_url( __FILE__ ) . '../js/',
				)
			);
			wp_enqueue_script( 'gifttest-questionaire' );
			wp_enqueue_style( 'gifttest-style' );

			return '<div id="gifttest"></div>';
		}
	}
endif;
