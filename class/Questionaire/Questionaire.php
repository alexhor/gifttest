<?php
namespace GiftTest\Questionaire;
use GiftTest\Questionaire\Element\ElementFactory;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Elements/ElementFactory.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Answer.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Talent.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Settings.php' );


class Questionaire implements \JsonSerializable {
	protected $settings;
	protected $elementList;
	protected $answerList;
	protected $talentList;

	function __construct( Settings $settings, array $elementList = [], array $answerList = [], array $talentList = [] ) {
		$this->settings = $settings;
		$this->elementList = $elementList;
		$this->answerList = $answerList;
		$this->talentList = $talentList;
	}

	public function save() {
		/*
		 * Save the questionaire in its current state to the database
		 * 
		 * @return bool: success or failure
		 */
		$success = true;
		// save settings
		if ( ! $this->settings->save() ) return false;

		/* save all other data */
		$dataToSave = [
			'element' => $this->elementList,
			'answer' => $this->answerList,
			'talent' => $this->talentList,
		];
		foreach ( $dataToSave as $instanceName => $instanceList ) {
			$resultList = [];
			foreach ( $instanceList as $instance ) {
				$localSuccess = $instance->save( $this->getId() );
				if ( $localSuccess ) $resultList[] = $instance->getId();
			}
			// check if the instance list has changed
			$currentInstanceList = get_option( 'gifttest_questionaire_' . (string) $this->getId() . '_' . $instanceName . '_list', [] );
			$isSame = $currentInstanceList === $resultList;
			// save instance list
			$success &= update_option( 'gifttest_questionaire_' . (string) $this->getId() . '_' . $instanceName . '_list', $resultList, false ) || $isSame;

			// delete obsolete instances
			foreach ( $currentInstanceList as $instanceId ) {
				if ( ! in_array( $instanceId, $resultList ) ) {
					delete_option( 'gifttest_questionaire_' . (string) $this->getId() . '_' . $instanceName . '_' . $instanceId );
				}
			}
		}

		return $success;
	}

	public function getId() {
		/*
		 * Get the questionaires' id
		 * 
		 * @return int: The questionaires' id
		 */
		return $this->settings->getId( 'id' );
	}

	public function getName() {
		/*
		 * Get the questionaires' name
		 * 
		 * @return string: The questionaires' name
		 */
		return $this->settings->getName();
	}

	public static function get( int $questionaireId ) {
		/*
		 * Get an existing Questionaire instance
		 * 
		 * @param int $questionaireId: The questionaires id in the database
		 * @return Questionaire|false: A Questionaire instance or false on error
		 */
		// get questionaire settings
		$settings = Settings::get( $questionaireId );
		if ( $settings == false ) return false;

		/* gather all instances from the database */
		$instanceTypeList = [ 'element', 'answer', 'talent' ];
		foreach ( $instanceTypeList as $instanceType ) {
			$instanceList = [];
			$instanceIdList = get_option( 'gifttest_questionaire_' . $questionaireId . '_' . $instanceType . '_list', [] );
			foreach ( $instanceIdList as $instanceId ) {
				// create instance
				switch ( $instanceType ) {
					case 'element':
						$instance = ElementFactory::get( $questionaireId, $instanceId );
						break;
					case 'answer':
						$instance = Answer::get( $questionaireId, $instanceId );
						break;
					case 'talent':
						$instance = Talent::get( $questionaireId, $instanceId );
						break;
				}
				if ( $instance === false ) continue;
				// add it to the data
				$instanceList[] = $instance;
			}

			// save fetched instances
			switch ( $instanceType ) {
				case 'element':
					$elementList = $instanceList;
					break;
				case 'answer':
					$answerList = $instanceList;
					break;
				case 'talent':
					$talentList = $instanceList;
					break;
			}
		}

		// generate the questionaire
		return new Questionaire( $settings, $elementList, $answerList, $talentList );
	}

	public static function create( string $name ) {
		/*
		 * Create a new questionaire instance
		 * 
		 * @param string $name: The questionaires name
		 * @return Questionaire|false: A Questionaire instance or false on error
		 */
		// generate new settings
		$settings = Settings::create( $name );
		// create a new questionaire from the settings and save it to the database
		$questionaire = new Questionaire( $settings, [] );
		$questionaire->save();
		// done
		return $questionaire;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the questionaire
		 * @return array
		 */
		return [
			'settings' => $this->settings,
			'elementList' => $this->elementList,
			'answerList' => $this->answerList,
			'talentList' => $this->talentList,
		];
	}

	public static function jsonDeserialize( array $jsonObject ) {
		/*
		 * Create an questionare instance from it's json representation
		 * 
		 * @param array $jsonObject: A questionaires json representation
		 * @return Questionaire|false: A Questionaire instance or false on error
		 */
		// deserialize settings
		if ( ! isset( $jsonObject['settings'] ) || ! is_array( $jsonObject['settings'] ) ) return false;
		$settings = Settings::jsonDeserialize( $jsonObject['settings'] );
		if ( $settings === false ) return false;

		// deserialize elements
		if ( ! isset( $jsonObject['elementList'] ) || ! is_array( $jsonObject['elementList'] ) ) $jsonObject['elementList'] = [];
		$elementList = [];
		foreach( $jsonObject['elementList'] as $elementJsonObject ) {
			$element = ElementFactory::jsonDeserialize( $elementJsonObject );
			if ( $element === false ) continue;
			else $elementList[] = $element;
		}

		// deserialize answers
		if ( ! isset( $jsonObject['answerList'] ) || ! is_array( $jsonObject['answerList'] ) ) $jsonObject['answerList'] = [];
		$answerList = [];
		foreach( $jsonObject['answerList'] as $answerJsonObject ) {
			$answer = Answer::jsonDeserialize( $answerJsonObject );
			if ( $answer === false ) continue;
			else $answerList[] = $answer;
		}

		// deserialize talents
		if ( ! isset( $jsonObject['talentList'] ) || ! is_array( $jsonObject['talentList'] ) ) $jsonObject['talentList'] = [];
		$talentList = [];
		foreach( $jsonObject['talentList'] as $talentJsonObject ) {
			$talent = Talent::jsonDeserialize( $talentJsonObject );
			if ( $talent === false ) continue;
			else $talentList[] = $talent;
		}

		// generate questionaire instance
		return new Questionaire( $settings, $elementList, $answerList, $talentList );
	}

	public function delete() {
		/*
		 * Delete this questionaire
		 * 
		 * @return bool: success or failure
		 */
		$questionaireId = $this->getId();

		// delete all instances from the database
		$this->elementList = [];
		$this->answerList = [];
		$this->talentList = [];
		if ( ! $this->save() ) return false;

		// delete all instance lists
		foreach ( [ 'element', 'answer', 'talent' ] as $instanceType ) {
			delete_option( 'gifttest_questionaire_' . $this->getId() . '_' . $instanceType . '_list', [] );
		}

		// remove the questionaires settings
		if ( ! $this->settings->delete() ) return false;

		// remove the questionaire itself
		$idList = Settings::getQuestionaireIdList();
		if ( ( $key = array_search( $questionaireId, $idList ) ) !== false ) unset( $idList[ $key ] );
		return Settings::updateQuestionaireIdList( $idList );
	}
}
