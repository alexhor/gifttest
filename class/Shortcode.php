<?php

namespace GiftTest;

if ( ! defined( 'ABSPATH' ) ) exit;

class Shortcode {
    /*
	 * Register the shortcode
	 */
	public static function register() {
		// register the shortcode
		add_shortcode( 'gifttest', [ Shortcode::class, 'renderShortcode' ] );
	}

	public static function renderShortcode( array $attributes ) {
		$attributes = shortcode_atts( [ 'test-id' => '1' ], $attributes );

		// get styles and scripts
		wp_localize_script( 'gifttest-questionaire', 'gifttest', [
			'test-id' => $attributes['test-id'],
			'plugin_dir_url' => plugin_dir_url( __DIR__ . '..' ),
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_ajax_nonce' => [
				'get_details' => wp_create_nonce( 'gifttest_get_questionaire_details' ),
			],
			'text' => [
				'continue' => esc_html__( 'Continue', 'gifttest' ),
				'back' => esc_html__( 'Back', 'gifttest' ),
				'scoreboard' => esc_html__( 'Scoreboard', 'gifttest' ),
				'printTalentList' => esc_html__( 'Print talent list', 'gifttest' ),
				'pointSingular' => esc_html__( 'Point', 'gifttest' ),
				'pointsPlural' => esc_html__( 'Points', 'gifttest' ),
				'showMoreTalents' => esc_html__( 'Show more talents', 'gifttest' ),
			],
		] );
		wp_enqueue_script( 'gifttest-questionaire' );
		wp_enqueue_style( 'gifttest-style' );

		return '<div id="gifttest"></div>';
	}
}
