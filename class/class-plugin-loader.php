<?php
/**
 * File Name: class-plugin-loader.php
 *
 * @package gifttest
 */

namespace Gift_Test;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-shortcode.php';
require_once plugin_dir_path( __FILE__ ) . 'class-settings-page.php';


if ( ! class_exists( 'Plugin_Loader' ) ) :
	/**
	 * Loads everything required for the plugin to run properly
	 *
	 * @since 1.0.0
	 */
	class Plugin_Loader {
		/**
		 * Registers all actions
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'init', array( $this, 'init' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'scripts' ) );
			new Settings_Page();
		}

		/**
		 * Initialise the plugin
		 *
		 * @since 1.0.0
		 */
		public function init() {
			$plugin_dir = dirname( plugin_basename( __DIR__ ) ) . '/lang';
			load_plugin_textdomain( 'gifttest', false, $plugin_dir );
			Shortcode::register();
		}

		/**
		 * Register scripts and styles for the admin panel
		 *
		 * @since 1.0.0
		 */
		public function admin_scripts() {
			wp_register_script( 'vuejs', plugin_dir_url( __FILE__ ) . '../js/vue.min.js', array(), '2.6.11', false );
			// Settings entry point.
			wp_register_script( 'gifttest-settings', plugin_dir_url( __FILE__ ) . '../js/gifttest_Settings.js', array( 'jquery', 'vuejs' ), '1.0.0', true );
			wp_localize_script(
				'gifttest-settings',
				'gifttest',
				array(
					'plugin_dir_url'      => plugin_dir_url( __DIR__ . '..' ),
					'_ajax_nonce'         => array(
						'create'                        => wp_create_nonce( 'gifttest_modify_questionaire' ),
						'update'                        => wp_create_nonce( 'gifttest_modify_questionaire' ),
						'delete'                        => wp_create_nonce( 'gifttest_delete_questionaire' ),
						'get_list'                      => wp_create_nonce( 'gifttest_get_questionaire_list' ),
						'get_details'                   => wp_create_nonce( 'gifttest_get_questionaire_details' ),
						'create_element'                => wp_create_nonce( 'gifttest_create_questionaire_element' ),
						'create_custom_question_answer' => wp_create_nonce( 'gifttest_create_questionaire_custom_question_answer' ),
						'create_answer'                 => wp_create_nonce( 'gifttest_create_questionaire_answer' ),
						'create_gift'                   => wp_create_nonce( 'gifttest_create_questionaire_gift' ),
					),
					'user_can_delete'     => current_user_can( 'delete_posts' ),
					'text'                => $this::settings_js_translations(),
					'vue_components_path' => plugin_dir_url( __FILE__ ) . '../js/',
				)
			);
			wp_register_style( 'gifttest-settings', plugin_dir_url( __FILE__ ) . '../css/settings.css', array(), '1.0.0' );
		}

		/**
		 * Register normal scripts and styles
		 *
		 * @since 1.0.0
		 */
		public function scripts() {
			wp_register_script( 'vuejs', plugin_dir_url( __FILE__ ) . '../js/vue.min.js', array(), '2.6.11', false );
			wp_register_script( 'gifttest-questionaire', plugin_dir_url( __FILE__ ) . '../js/gifttest_Questionaire.js', array( 'jquery', 'vuejs' ), '1.0.0', false );
			wp_register_style( 'gifttest-style', plugin_dir_url( __FILE__ ) . '../css/style.css', array(), '1.0.0' );
		}

		/**
		 * Get an array of all translations for the settings javascript
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		private static function settings_js_translations() {
			return array(
				'load_questionaire'                  => esc_html__( 'Load Questionaire', 'gifttest' ),
				'no_questionaire_exists_yet'         => esc_html__( 'No questionaire exists yet', 'gifttest' ),
				'add_questionaire'                   => esc_html__( 'Add Questionaire', 'gifttest' ),
				'questionaire_name'                  => esc_html__( 'Questionaire Name', 'gifttest' ),
				'edit_questionaire'                  => esc_html__( 'Edit Questionaire', 'gifttest' ),
				'name'                               => esc_html__( 'Name', 'gifttest' ),
				'number_of_gifts_shown'              => esc_html__( 'Number of gifts shown', 'gifttest' ),
				'enable_show_more_gifts_button'      => esc_html__( 'Enabel show more gifts button', 'gifttest' ),
				'yes'                                => esc_html__( 'Yes', 'gifttest' ),
				'no'                                 => esc_html__( 'No', 'gifttest' ),
				'elements'                           => esc_html__( 'Elements', 'gifttest' ),
				'add_element'                        => esc_html__( 'Add Element', 'gifttest' ),
				'text'                               => esc_html__( 'Text', 'gifttest' ),
				'question'                           => esc_html__( 'Question', 'gifttest' ),
				'delete_element'                     => esc_html__( 'Delete Element', 'gifttest' ),
				'custom_question'                    => esc_html__( 'Custom Question', 'gifttest' ),
				'answers'                            => esc_html__( 'Answers', 'gifttest' ),
				'answer'                             => esc_html__( 'Answer', 'gifttest' ),
				'answer_content'                     => esc_html__( 'Answer Content', 'gifttest' ),
				'answer_value'                       => esc_html__( 'Answer Value', 'gifttest' ),
				'add_answer'                         => esc_html__( 'Add Answer', 'gifttest' ),
				'edit_answers'                       => esc_html__( 'Edit Answers', 'gifttest' ),
				'gifts'                              => esc_html__( 'Gifts', 'gifttest' ),
				'gift'                               => esc_html__( 'Gift', 'gifttest' ),
				'edit_gifts'                         => esc_html__( 'Edit Gifts', 'gifttest' ),
				'gift_title'                         => esc_html__( 'Gift Title', 'gifttest' ),
				'gift_text'                          => esc_html__( 'Gift Text', 'gifttest' ),
				'add_gift'                           => esc_html__( 'Add Gift', 'gifttest' ),
				'question_list'                      => esc_html__( 'Question List', 'gifttest' ),
				'add_question'                       => esc_html__( 'Add Question', 'gifttest' ),
				'content'                            => esc_html__( 'Content', 'gifttest' ),
				'invalid_element'                    => esc_html__( 'Invalid Element', 'gifttest' ),
				'save'                               => esc_html__( 'Save', 'gifttest' ),
				'delete'                             => esc_html__( 'Delete', 'gifttest' ),
				'export'                             => esc_html__( 'Export', 'gifttest' ),
				'import_questionaire'                => esc_html__( 'Import Questionaire', 'gifttest' ),
				'import_failed_invalid_file_content' => esc_html__( 'Import Failed - Invalid File Content', 'gifttest' ),
				'import_successful_save_the_questionaire_if_you_want_to_keep_it' => esc_html__( 'Import Successful - Save the questionaire if you want to keep it', 'gifttest' ),
				'shortcode'                          => esc_html__( 'Shortcode', 'gifttest' ),
				'copy'                               => esc_html__( 'Copy', 'gifttest' ),
				'copy_to_clipboard'                  => esc_html__( 'Copy to clipboard', 'gifttest' ),
				'copied'                             => esc_html__( 'Copied!', 'gifttest' ),
				'edit_elements'                      => esc_html__( 'Edit Elements', 'gifttest' ),
				'really_delete_questionaire'         => esc_html__( 'Do you really want to delete this questionaire?', 'gifttest' ),
			);
		}
	}
endif;
