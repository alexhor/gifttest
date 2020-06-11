<?php
/**
 * File Name: class-settings-page.php
 *
 * @package gifttest
 */

namespace Gift_Test;

use Gift_Test\Questionaire\Questionaire;
use Gift_Test\Questionaire\Answer;
use Gift_Test\Questionaire\Gift;
use Gift_Test\Questionaire\Element\Element_Factory;
use Gift_Test\Questionaire\Element\Element_Type;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'questionaire/class-questionaire.php';
require_once plugin_dir_path( __FILE__ ) . 'questionaire/class-answer.php';
require_once plugin_dir_path( __FILE__ ) . 'questionaire/class-gift.php';
require_once plugin_dir_path( __FILE__ ) . 'questionaire/elements/class-element-factory.php';
require_once plugin_dir_path( __FILE__ ) . 'questionaire/elements/class-element-type.php';


if ( ! class_exists( 'Settings_Page' ) ) :
	/**
	 * A handler for all settings
	 *
	 * @since 1.0.0
	 */
	class Settings_Page {
		/**
		 * Register all actions
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			add_action( 'admin_menu', array( $this, 'register' ) );
			if ( is_admin() ) {
				// Admin requests.
				add_action( 'wp_ajax_gifttest_create_questionaire', array( $this, 'create_questionaire' ) );
				add_action( 'wp_ajax_gifttest_update_questionaire', array( $this, 'update_questionaire' ) );
				add_action( 'wp_ajax_gifttest_delete_questionaire', array( $this, 'delete_questionaire' ) );
				add_action( 'wp_ajax_gifttest_get_questionaire_list', array( $this, 'get_questionaire_list_json' ) );
				add_action( 'wp_ajax_gifttest_get_questionaire_details', array( $this, 'get_questionaire_details_json' ) );
				add_action( 'wp_ajax_gifttest_create_questionaire_element', array( $this, 'create_element_json' ) );
				add_action( 'wp_ajax_gifttest_create_questionaire_custom_question_answer', array( $this, 'create_custom_question_answer_json' ) );
				add_action( 'wp_ajax_gifttest_create_questionaire_answer', array( $this, 'create_answer_json' ) );
				add_action( 'wp_ajax_gifttest_create_questionaire_gift', array( $this, 'create_gift_json' ) );
				// front page requests.
				add_action( 'wp_ajax_nopriv_gifttest_get_questionaire_details', array( $this, 'get_questionaire_details_json' ) );
			}
		}

		/**
		 * Register a settings page for this plugin
		 *
		 * @since 1.0.0
		 */
		public function register() {
			if ( current_user_can( 'edit_posts' ) ) {
				add_options_page(
					__( 'Gift Test', 'gifttest' ),
					__( 'Gift Test', 'gifttest' ),
					'manage_options',
					'gifttest',
					array( $this, 'render' )
				);
			}
		}

		/**
		 * Render the options page
		 *
		 * @since 1.0.0
		 */
		public function render() {
			?>
			<div class="wrap">
				<h2><?php esc_html_e( 'Gift Test', 'gifttest' ); ?></h2>
				<div id="gifttest" />
				<?php do_settings_sections( 'gifttest' ); ?>
			</div>
			<?php
			wp_enqueue_script( 'gifttest-settings' );
			wp_enqueue_style( 'gifttest-settings' );
			wp_enqueue_editor();
		}

		/**
		 * Check if a user can modify gift tests
		 *
		 * @since 1.0.0
		 */
		protected static function current_user_can_modify_gift_tests() {
			return check_ajax_referer( 'gifttest_modify_questionaire' ) && current_user_can( 'edit_posts' );
		}

		/**
		 * Create a json response with the given data
		 *
		 * @param bool  $success Wether the request was a success.
		 * @param array $data The data to send with the response.
		 *
		 * @since 1.0.0
		 */
		protected static function json_response( bool $success = true, array $data = array() ) {
			header( 'Content-Type: application/json' );
			// Compose data.
			$status = $success ? 'success' : 'error';
			$data   = array_merge( $data, array( 'status' => $status ) );
			// Output data.
			wp_send_json( $data, 200 );
		}

		/**
		 * Create a new Questionaire
		 *
		 * @since 1.0.0
		 */
		public function create_questionaire() {
			// Check permissions and origin.
			if ( ! $this::current_user_can_modify_gift_tests() || ! check_ajax_referer( 'gifttest_modify_questionaire' ) || ! isset( $_REQUEST['name'] ) ) {
				wp_die( 'Invalid request' );
			}
			if ( isset( $_REQUEST['name'] ) ) {
				return $this::json_response( false, array() );
			}

			$name = sanitize_text_field( wp_unslash( $_REQUEST['name'] ) );
			// Create questionaire.
			$questionaire = Questionaire::create( $name );

			// Return response.
			$this::json_response(
				true,
				array(
					// translators: "%s" is the questionaires name.
					'message'           => sprintf( __( 'Questionaire "%s" created', 'gifttest' ), $questionaire->get_name() ),
					'questionaire_name' => $questionaire->get_name(),
					'questionaire_id'   => $questionaire->get_id(),
				)
			);
		}

		/**
		 * Update an existing questionaire
		 *
		 * @since 1.0.0
		 */
		public function update_questionaire() {
			// Check permissions and origin.
			if ( ! $this::current_user_can_modify_gift_tests() || ! check_ajax_referer( 'gifttest_modify_questionaire' ) || ! isset( $_REQUEST['questionaire_as_json'] ) || ! is_array( $_REQUEST['questionaire_as_json'] ) ) {
				wp_die( 'Invalid request' );
			}
			// Create questionaire instance.
			// phpcs:ignore
			$questionaire = Questionaire::json_deserialize( wp_unslash( $_REQUEST['questionaire_as_json'] ) );

			// Check questionaire validity.
			if ( false === $questionaire ) {
				return $this::json_response( false, array( 'message' => __( 'Invalid data', 'gifttest' ) ) );
			}

			// Save questionaire to database.
			$success = $questionaire->save();

			// Create response.
			if ( $success ) {
				return $this::json_response( true, array( 'message' => __( 'Questionaire saved', 'gifttest' ) ) );
			} else {
				return $this::json_response( false, array( 'message' => __( 'Saving questionaire failed', 'gifttest' ) ) );
			}
		}

		/**
		 * Get a list of all existing questionaires
		 *
		 * @return array(Questionaire) A list of all existing Questionaires
		 *
		 * @since 1.0.0
		 */
		public function get_questionaire_list() {
			$questionaire_list = array();
			$id_list           = get_option( 'gifttest_questionaire_id_list', array() );
			foreach ( $id_list as $questionaire_id ) {
				// Load and validate the questionaire.
				$questionaire = Questionaire::get( $questionaire_id );
				if ( false === $questionaire ) {
					continue;
				}
				$questionaire_list[] = $questionaire;
			}
			// Return existing questionaires.
			return $questionaire_list;
		}

		/**
		 * Get a list of all existing questionaires and return it as a json response
		 *
		 * @since 1.0.0
		 */
		public function get_questionaire_list_json() {
			// Check request reference.
			if ( ! check_ajax_referer( 'gifttest_get_questionaire_list' ) ) {
				wp_die( 'Invalid request' );
			}
			// Get all questionaires.
			$list_for_json     = array();
			$questionaire_list = $this->get_questionaire_list();
			foreach ( $questionaire_list as $questionaire ) {
				$list_for_json[] = array(
					'name' => $questionaire->get_name(),
					'id'   => $questionaire->get_id(),
				);
			}

			// Create response.
			return $this::json_response(
				true,
				array(
					'data'    => $list_for_json,
					'message' => __(
						'Questionaires successfully loaded',
						'gifttest'
					),
				)
			);
		}

		/**
		 * Get the details of a specific questionaire
		 *
		 * @since 1.0.0
		 */
		public function get_questionaire_details_json() {
			// Check request reference and parse questionaire id.
			if ( ! check_ajax_referer( 'gifttest_get_questionaire_details' ) || ! isset( $_REQUEST['id'] ) || ! is_numeric( $_REQUEST['id'] ) ) {
				wp_die( 'Invalid request' );
			}
			// Load and validate the questionaire.
			$questionaire = Questionaire::get( sanitize_text_field( wp_unslash( $_REQUEST['id'] ) ) );
			// create response.
			if ( false === $questionaire ) {
				return $this::json_response( false, array( 'message' => __( 'Questionaire details loading failed', 'gifttest' ) ) );
			} else {
				return $this::json_response(
					true,
					array(
						'data'    => $questionaire,
						'message' => __(
							'Questionaire details successfully loaded',
							'gifttest'
						),
					)
				);
			}
		}

		/**
		 * Create a new element of the given type
		 *
		 * @since 1.0.0
		 */
		public function create_element_json() {
			// check request reference and parse arguments.
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_element' )
				|| ! isset( $_REQUEST['type'] ) || ! is_numeric( $_REQUEST['type'] )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $_REQUEST['id'] )
				|| ! isset( $_REQUEST['questionaire_id'] ) || ! is_numeric( $_REQUEST['questionaire_id'] )
			) {
				wp_die( 'Invalid request' );
			}
			$type            = (int) sanitize_text_field( wp_unslash( $_REQUEST['type'] ) );
			$id              = (int) sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
			$questionaire_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['questionaire_id'] ) );

			// Create element.
			$element = Element_Factory::create( $questionaire_id, $id, $type );
			// Create response.
			if ( false === $element ) {
				return $this::json_response( false, array( 'message' => __( 'Creating element failed', 'gifttest' ) ) );
			} else {
				return $this::json_response(
					true,
					array(
						'data'    => $element,
						'message' => __(
							'Element created',
							'gifttest'
						),
					)
				);
			}
		}

		/**
		 * Create a new answer for a custom question
		 *
		 * @since 1.0.0
		 */
		public function create_custom_question_answer_json() {
			// Check request reference and parse arguments.
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_custom_question_answer' )
				|| ! isset( $_REQUEST['answer_id'] ) || ! is_numeric( $_REQUEST['answer_id'] )
				|| ! isset( $_REQUEST['question_id'] ) || ! is_numeric( $_REQUEST['question_id'] )
				|| ! isset( $_REQUEST['questionaire_id'] ) || ! is_numeric( $_REQUEST['questionaire_id'] )
			) {
				wp_die( 'Invalid request' );
			}
			$answer_id       = (int) sanitize_text_field( wp_unslash( $_REQUEST['answer_id'] ) );
			$question_id     = (int) sanitize_text_field( wp_unslash( $_REQUEST['question_id'] ) );
			$questionaire_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['questionaire_id'] ) );

			// Het the element or create if it doesn't exist.
			$element = Element_Factory::get( $questionaire_id, $question_id );
			if ( false === $element ) {
				$element = Element_Factory::create( $questionaire_id, $question_id, Element_Type::Custom_Question );
			}
			// The element has to be a custom question.
			if ( false === $element || $element->get_type() !== Element_Type::Custom_Question ) {
				return $this::json_response( false, array( 'message' => __( 'Creating answer failed', 'gifttest' ) ) );
			}

			// Create a new answer.
			$answer = $element->get_answer( $answer_id );
			return $this::json_response(
				true,
				array(
					'data'    => $answer,
					'message' => __(
						'Answer created',
						'gifttest'
					),
				)
			);
		}

		/**
		 * Create a new answer
		 *
		 * @since 1.0.0
		 */
		public function create_answer_json() {
			// check request reference and parse arguments.
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_answer' )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $_REQUEST['id'] )
				|| ! isset( $_REQUEST['questionaire_id'] ) || ! is_numeric( $_REQUEST['questionaire_id'] )
			) {
				wp_die( 'Invalid request' );
			}
			$id              = (int) sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
			$questionaire_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['questionaire_id'] ) );

			// Create answer.
			$answer = Answer::get( $questionaire_id, $id );
			// Create response.
			if ( false === $answer ) {
				return $this::json_response( false, array( 'message' => __( 'Creating answer failed', 'gifttest' ) ) );
			} else {
				return $this::json_response(
					true,
					array(
						'data'    => $answer,
						'message' => __(
							'Answer created',
							'gifttest'
						),
					)
				);
			}
		}

		/**
		 * Create a new gift
		 *
		 * @since 1.0.0
		 */
		public function create_gift_json() {
			// Check request reference and parse arguments.
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_gift' )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $_REQUEST['id'] )
				|| ! isset( $_REQUEST['questionaire_id'] ) || ! is_numeric( $_REQUEST['questionaire_id'] )
			) {
				wp_die( 'Invalid request' );
			}
			$id              = (int) sanitize_text_field( wp_unslash( $_REQUEST['id'] ) );
			$questionaire_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['questionaire_id'] ) );

			// Create gift.
			$gift = Gift::get( $questionaire_id, $id );
			// Create response.
			if ( false === $gift ) {
				return $this::json_response( false, array( 'message' => __( 'Creating gift failed', 'gifttest' ) ) );
			} else {
				return $this::json_response(
					true,
					array(
						'data'    => $gift,
						'message' => __(
							'Gift created',
							'gifttest'
						),
					)
				);
			}
		}

		/**
		 * Delete a questionaire
		 *
		 * @since 1.0.0
		 */
		public function delete_questionaire() {
			// Check request validity and permissions.
			if ( ! check_ajax_referer( 'gifttest_delete_questionaire' ) || ! isset( $_REQUEST['questionaire_id'] ) || ! is_numeric( $_REQUEST['questionaire_id'] ) ) {
				wp_die( 'Invalid request' );
			}
			$questionaire_id = (int) sanitize_text_field( wp_unslash( $_REQUEST['questionaire_id'] ) );

			if ( ! current_user_can( 'delete_posts' ) ) {
				return $this::json_response( false, array( 'message' => __( 'Permission denied', 'gifttest' ) ) );
			}

			// Delete questionaire.
			$questionaire = Questionaire::get( $questionaire_id );
			if ( $questionaire->delete() ) {
				return $this::json_response( true, array( 'message' => __( 'Questionaire deleted', 'gifttest' ) ) );
			} else {
				return $this::json_response( false, array( 'message' => __( 'Deleting questionaire failed', 'gifttest' ) ) );
			}
		}
	}
endif;
