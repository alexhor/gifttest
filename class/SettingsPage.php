<?php
namespace GiftTest;
use GiftTest\Questionaire\Questionaire;
use GiftTest\Questionaire\Answer;
use GiftTest\Questionaire\Talent;
use GiftTest\Questionaire\Element\ElementFactory;
use GiftTest\Questionaire\Element\ElementType;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Questionaire/Questionaire.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Questionaire/Answer.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Questionaire/Talent.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Questionaire/Elements/ElementFactory.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Questionaire/Elements/ElementType.php' );


if ( ! class_exists( 'SettingsPage' ) ) :
	class SettingsPage {
		function __construct() {
			add_action( 'admin_menu', [ $this, 'register' ] );
			if ( is_admin() ) {
				// admin requests
				add_action( 'wp_ajax_gifttest_create_questionaire', [ $this, 'createQuestionaire' ] );
				add_action( 'wp_ajax_gifttest_update_questionaire', [ $this, 'updateQuestionaire' ] );
				add_action( 'wp_ajax_gifttest_delete_questionaire', [ $this, 'deleteQuestionaire' ] );
				add_action( 'wp_ajax_gifttest_get_questionaire_list', [ $this, 'getQuestionaireListJson' ] );
				add_action( 'wp_ajax_gifttest_get_questionaire_details', [ $this, 'getQuestionaireDetailsJson' ] );
				add_action( 'wp_ajax_gifttest_create_questionaire_element', [ $this, 'createElementJson' ] );
				add_action( 'wp_ajax_gifttest_create_questionaire_custom_question_answer', [ $this, 'createCustomQuestionAnswerJson' ] );
				add_action( 'wp_ajax_gifttest_create_questionaire_answer', [ $this, 'createAnswerJson' ] );
				add_action( 'wp_ajax_gifttest_create_questionaire_talent', [ $this, 'createTalentJson' ] );
				// front page requests
				add_action( 'wp_ajax_nopriv_gifttest_get_questionaire_details', [ $this, 'getQuestionaireDetailsJson' ] );
			}
		}

		public function register() {
			/*
			 * Register a settings page for this plugin
			 */
			if ( current_user_can( 'edit_posts' ) ) {
				add_options_page(
					__( 'Gift Test', 'gifttest' ),
					__( 'Gift Test', 'gifttest' ),
					'manage_options',
					'gifttest',
					[ $this, 'render' ]
				);
			}
		}

		public function render() {
			/*
			 * Render the options page
			 */
			?>
			<div class="wrap">
				<h2><?php _e( 'Gift Test', 'gifttest' ); ?></h2>
				<div id="gifttest" />
				<?php do_settings_sections( 'gifttest' ); ?>
			</div>
			<?php
			wp_enqueue_script( 'gifttest-settings' );
			wp_enqueue_style( 'gifttest-settings' );
			wp_enqueue_editor();
		}

		protected static function currentUserCanModifyGiftTests() {
			/*
			 * Check if a user can modify gift tests
			 */
			return check_ajax_referer( 'gifttest_modify_questionaire' ) && current_user_can( 'edit_posts' );
		}

		protected static function jsonResponse( bool $success = true, array $data = [] ) {
			/*
			 * create a json response with the given data
			 */
			header( 'Content-Type: application/json' );
			// compose data
			$status = $success ? 'success' : 'error';
			$data = array_merge( $data, [ 'status' => $status ] );
			// output data
			echo json_encode( $data );
			wp_die();
		}

		public function createQuestionaire() {
			/*
			 * Create a new Questionaire
			 */
			// check permissions and origin
			if ( ! $this::currentUserCanModifyGiftTests() || ! isset( $_POST['name'] ) || empty( trim( $_POST['name'] ) ) ) wp_die( 'Invalid request' );

			$name = trim( $_POST['name'] );
			// create questionaire
			$questionaire = Questionaire::create( $name );

			// return response
			$this::jsonResponse( true, [
				'message' => sprintf( __( 'Questionaire "%s" created', 'gifttest' ), $questionaire->getName() ),
				'questionaireName' => $questionaire->getName(),
				'questionaireId' => $questionaire->getId(),
			] );
		}

		public function updateQuestionaire() {
			/*
			 * Update an existing questionaire
			 */
			// check permissions and origin
			if ( ! $this::currentUserCanModifyGiftTests() || ! isset( $_POST['questionaireAsJson'] ) || ! is_array( $_POST['questionaireAsJson'] ) ) wp_die( 'Invalid request' );
			// create questionaire instance
			$questionaire = Questionaire::jsonDeserialize( $_POST['questionaireAsJson'] );

			// check questionaire validity
			if ( $questionaire === false ) return $this::jsonResponse( false, [ 'message' => __( 'Invalid data', 'gifttest' ) ] );

			// save questionaire to database
			$success = $questionaire->save();

			// create response
			if ( $success ) return $this::jsonResponse( true, [ 'message' => __( 'Questionaire saved', 'gifttest' ) ] );
			else return $this::jsonResponse( false, [ 'message' => __( 'Saving questionaire failed', 'gifttest' ) ] );
		}
		
		public function getQuestionaireList() {
			/*
			 * Get a list of all existing questionaires
			 * 
			 * @return Questionaire[]: A list of all existing Questionaires
			 */
			$questionaireList = [];
			$idList = get_option( 'gifttest_questionaire_id_list', [] );
			foreach ( $idList as $questionaireId ) {
				// load and validate the questionaire
				$questionaire = Questionaire::get( $questionaireId );
				if ( $questionaire === false ) continue;
				$questionaireList[] = $questionaire;
			}
			// return existing questionaires
			return $questionaireList;
		}

		function getQuestionaireListJson() {
			/*
			 * Get a list of all existing questionaires and return it as a json response
			 */
			// check request reference
			if ( ! check_ajax_referer( 'gifttest_get_questionaire_list' ) ) wp_die( 'Invalid request' );
			// get all questionaires
			$listForJson = [];
			$questionaireList = $this->getQuestionaireList();
			foreach ( $questionaireList as $questionaire ) {
				$listForJson[] = [ 'name' => $questionaire->getName(), 'id' => $questionaire->getId() ];
			}

			// create response
			return $this::jsonResponse( true, [ 'data' => $listForJson, 'message' => __( 'Questionaires successfully loaded', 'gifttest' ) ] );
		}

		public function getQuestionaireDetailsJson() {
			/*
			 * Get the details of a specific questionaire
			 */
			// check request reference and parse questionaire id
			if ( ! check_ajax_referer( 'gifttest_get_questionaire_details' ) || ! isset( $_REQUEST['id'] ) || ! is_numeric( $id = trim( $_REQUEST['id'] ) ) ) wp_die( 'Invalid request' );
			// load and validate the questionaire
			$questionaire = Questionaire::get( $id );
			// create response
			if ( $questionaire === false ) return $this::jsonResponse( false, [ 'message' => __( 'Questionaire details loading failed', 'gifttest' ) ] );
			else return $this::jsonResponse( true, [ 'data' => $questionaire, 'message' => __( 'Questionaire details successfully loaded', 'gifttest' ) ] );
		}

		public function createElementJson() {
			/*
			 * Create a new element of the given type
			 */
			// check request reference and parse arguments
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_element' )
				|| ! isset( $_REQUEST['type'] ) || ! is_numeric( $type = trim( $_REQUEST['type'] ) )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $id = trim( $_REQUEST['id'] ) )
				|| ! isset( $_REQUEST['questionaireId'] ) || ! is_numeric( $questionaireId = trim( $_REQUEST['questionaireId'] ) )
			) wp_die( 'Invalid request' );
			// create element
			$element = ElementFactory::create( $questionaireId, $id, $type );
			// create response
			if ( $element === false ) return $this::jsonResponse( false, [ 'message' => __( 'Creating element failed', 'gifttest' ) ] );
			else return $this::jsonResponse( true, [ 'data' => $element, 'message' => __( 'Element created', 'gifttest' ) ] );
		}

		public function createCustomQuestionAnswerJson() {
			/*
			 * Create a new answer for a custom question
			 */
			// check request reference and parse arguments
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_custom_question_answer' )
				|| ! isset( $_REQUEST['answerId'] ) || ! is_numeric( $answerId = trim( $_REQUEST['answerId'] ) )
				|| ! isset( $_REQUEST['questionId'] ) || ! is_numeric( $questionId = trim( $_REQUEST['questionId'] ) )
				|| ! isset( $_REQUEST['questionaireId'] ) || ! is_numeric( $questionaireId = trim( $_REQUEST['questionaireId'] ) )
			) wp_die( 'Invalid request' );
			// get the element or create if it doesn't exist
			$element = ElementFactory::get( $questionaireId, $questionId );
			if ($element === false) $element = ElementFactory::create( $questionaireId, $questionId, ElementType::CustomQuestion );
			// the element has to be a custom question
			if ( $element === false || $element->getType() !== ElementType::CustomQuestion ) return $this::jsonResponse( false, [ 'message' => __( 'Creating answer failed', 'gifttest' ) ] );

			// create a new answer
			$answer = $element->getAnswer( $answerId );
			return $this::jsonResponse( true, [ 'data' => $answer, 'message' => __( 'Answer created', 'gifttest' ) ] );
		}

		public function createAnswerJson() {
			/*
			 * Create a new answer
			 */
			// check request reference and parse arguments
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_answer' )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $id = trim( $_REQUEST['id'] ) )
				|| ! isset( $_REQUEST['questionaireId'] ) || ! is_numeric( $questionaireId = trim( $_REQUEST['questionaireId'] ) )
			) wp_die( 'Invalid request' );
			// create answer
			$answer = Answer::get( $questionaireId, $id );
			// create response
			if ( $answer === false ) return $this::jsonResponse( false, [ 'message' => __( 'Creating answer failed', 'gifttest' ) ] );
			else return $this::jsonResponse( true, [ 'data' => $answer, 'message' => __( 'Answer created', 'gifttest' ) ] );
		}

		public function createTalentJson() {
			/*
			 * Create a new talent
			 */
			// check request reference and parse arguments
			if ( ! check_ajax_referer( 'gifttest_create_questionaire_talent' )
				|| ! isset( $_REQUEST['id'] ) || ! is_numeric( $id = trim( $_REQUEST['id'] ) )
				|| ! isset( $_REQUEST['questionaireId'] ) || ! is_numeric( $questionaireId = trim( $_REQUEST['questionaireId'] ) )
			) wp_die( 'Invalid request' );
			// create talent
			$talent = Talent::get( $questionaireId, $id );
			// create response
			if ( $talent === false ) return $this::jsonResponse( false, [ 'message' => __( 'Creating talent failed', 'gifttest' ) ] );
			else return $this::jsonResponse( true, [ 'data' => $talent, 'message' => __( 'Talent created', 'gifttest' ) ] );
		}
		
		public function deleteQuestionaire() {
			/*
			 * Delete a questionaire
			 */
			// check request validity and permissions
			if ( ! check_ajax_referer( 'gifttest_delete_questionaire' ) || ! isset( $_POST['questionaireId'] ) || ! is_numeric( $_POST['questionaireId'] ) ) wp_die( 'Invalid request' );
			if ( ! current_user_can( 'delete_posts' ) ) return $this::jsonResponse( false, [ 'message' => __( 'Permission denied', 'gifttest' ) ] );

			// delete questionaire
			$questionaire = Questionaire::get( $_POST['questionaireId'] );
			if ( $questionaire->delete() ) return $this::jsonResponse( true, [ 'message' => __( 'Questionaire deleted', 'gifttest' ) ] );
			else return $this::jsonResponse( false, [ 'message' => __( 'Deleting questionaire failed', 'gifttest' ) ] );
		}
	}
endif;
