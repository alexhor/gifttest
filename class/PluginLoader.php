<?php

namespace GiftTest;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Shortcode.php' );
require_once( plugin_dir_path( __FILE__ ) . 'SettingsPage.php' );

class PluginLoader {
	function __construct() {
		add_action( 'init', [ $this, 'init' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'adminScripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts' ] );
		new SettingsPage;
	}

	public function setup() {
		/*
		 * Run plugin first time setup
		 */
	}
	
	public function init() {
		/*
		 * Initialise the plugin
		 */
		// load test domain
		load_plugin_textdomain( 'gifttest', false, 'lang/' );
		Shortcode::register();
	}
	
	public function adminScripts() {
		/*
		 * Register scripts and styles for the admin panel
		 */
		wp_register_script( 'gifttest-settings', plugin_dir_url( __FILE__ ) . '../js/GiftTest_Settings.js' );
		wp_localize_script( 'gifttest-settings', 'gifttest', [
			'plugin_dir_url' => plugin_dir_url( __DIR__ . '..' ),
			'_ajax_nonce' => [
				'create' => wp_create_nonce( 'gifttest_modify_questionaire' ),
				'update' => wp_create_nonce( 'gifttest_modify_questionaire' ),
				'delete' => wp_create_nonce( 'gifttest_delete_questionaire' ),
				'get_list' => wp_create_nonce( 'gifttest_get_questionaire_list' ),
				'get_details' => wp_create_nonce( 'gifttest_get_questionaire_details' ),
				'create_element' => wp_create_nonce( 'gifttest_create_questionaire_element' ),
				'create_custom_question_answer' => wp_create_nonce( 'gifttest_create_questionaire_custom_question_answer' ),
				'create_answer' => wp_create_nonce( 'gifttest_create_questionaire_answer' ),
				'create_talent' => wp_create_nonce( 'gifttest_create_questionaire_talent' ),
			],
			'user_can_delete' => current_user_can( 'delete_posts' ),
			'text' => $this::SettingsJsTranslations(),
		] );
		wp_register_style( 'gifttest-settings', plugin_dir_url( __FILE__ ) . '../css/settings.css' );
	}

	public function scripts() {
		/*
		 * Register normal scripts and styles
		 */
		wp_register_script( 'gifttest-questionaire', plugin_dir_url( __FILE__ ) . '../js/GiftTest_Questionaire.js' );
		wp_register_style( 'gifttest-style', plugin_dir_url( __FILE__ ) . '../css/style.css' );
	}

	private static function SettingsJsTranslations() {
		/*
		 * Get an array of all translations for the settings javascript
		 * 
		 * @return array
		 */
		return [
			'loadQuestionaire' => esc_html__( 'Load Questionaire', 'gifttest' ),
			'noQuestionaireExistsYes' => esc_html__( 'No questionaire exists yet', 'gifttest' ),
			'addQuestionaire' => esc_html__( 'Add Questionaire', 'gifttest' ),
			'questionaireName' => esc_html__( 'Questionaire Name', 'gifttest' ),
			'editQuestionaire' => esc_html__( 'Edit Questionaire', 'gifttest' ),
			'name' => esc_html__( 'Name', 'gifttest' ),
			'numberOfTalentsShown' => esc_html__( 'Number of talents shown', 'gifttest' ),
			'enableShowMoreTalentsButton' => esc_html__( 'Enabel show more talents button', 'gifttest' ),
			'yes' => esc_html__( 'Yes', 'gifttest' ),
			'no' => esc_html__( 'No', 'gifttest' ),
			'elements' => esc_html__( 'Elements', 'gifttest' ),
			'addElement' => esc_html__( 'Add Element', 'gifttest' ),
			'text' => esc_html__( 'Text', 'gifttest' ),
			'question' => esc_html__( 'Question', 'gifttest' ),
			'deleteElement' => esc_html__( 'Delete Element', 'gifttest' ),
			'customQuestion' => esc_html__( 'Custom Question', 'gifttest' ),
			'answers' => esc_html__( 'Answers', 'gifttest' ),
			'answer' => esc_html__( 'Answer', 'gifttest' ),
			'answerContent' => esc_html__( 'Answer Content', 'gifttest' ),
			'answerValue' => esc_html__( 'Answer Value', 'gifttest' ),
			'addAnswer' => esc_html__( 'Add Answer', 'gifttest' ),
			'editAnswers' => esc_html__( 'Edit Answers', 'gifttest' ),
			'talents' => esc_html__( 'Talents', 'gifttest' ),
			'talent' => esc_html__( 'Talent', 'gifttest' ),
			'editTalents' => esc_html__( 'Edit Talents', 'gifttest' ),
			'talentTitle' => esc_html__( 'Talent Title', 'gifttest' ),
			'talentText' => esc_html__( 'Talent Text', 'gifttest' ),
			'addTalent' => esc_html__( 'Add Talent', 'gifttest' ),
			'questionList' => esc_html__( 'Question List', 'gifttest' ),
			'addQuestion' => esc_html__( 'Add Question', 'gifttest' ),
			'content' => esc_html__( 'Content', 'gifttest' ),
			'invalidElement' => esc_html__( 'Invalid Element', 'gifttest' ),
			'save' => esc_html__( 'Save', 'gifttest' ),
			'delete' => esc_html__( 'Delete', 'gifttest' ),
			'export' => esc_html__( 'Export', 'gifttest' ),
			'importQuestionaire' => esc_html__( 'Import Questionaire', 'gifttest' ),
			'importFailed_invalidFileContent' => esc_html__( 'Import Failed - Invalid File Content', 'gifttest' ),
			'importSuccessful_saveTheQuestionaireIfYouWantToKeepIt' => esc_html__( 'Import Successful - Save the questionaire if you want to keep it', 'gifttest' ),
			'shortcode' => esc_html__( 'Shortcode', 'gifttest' ),
			'copy' => esc_html__( 'Copy', 'gifttest' ),
			'copyToClipboard' => esc_html__( 'Copy to clipboard', 'gifttest' ),
			'copied' => esc_html__( 'Copied!', 'gifttest' ),
			'editElements' => esc_html__( 'Edit Elements', 'gifttest' ),
			'reallyDeleteQuestionaire' => esc_html__( 'Do you really want to delete this questionaire?', 'gifttest' ),
		];
	}
}
