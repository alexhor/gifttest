<?php
namespace GiftTest\Questionaire;

if ( ! defined( 'ABSPATH' ) ) exit;


class Settings implements \JsonSerializable {
	protected $settings;
	const intVals = [ 'id', 'shownTalentCount' ];

	function __construct( array $settings ) {
		/*
		 * @param array $settings: All settings for this questionaire
		 */
		$this->settings = $settings;

		foreach ( self::intVals as $key ) {
			if ( isset ( $this->settings[ $key ] ) ) $this->settings[ $key ] = (int) $this->settings[ $key ];
		}
	}

	public function save() {
		/*
		 * Save the questionaires' settings
		 * 
		 * @return bool: success or failure
		 */
		// make sure the questionaire is registered
		$idList = $this::getQuestionaireIdList();
		if ( ! in_array( $this->getId(), $idList ) ) {
			$idList[] = $this->getId();
			if ( ! $this::updateQuestionaireIdList( $idList ) ) return false;
		}
		// check if the settings have changed
		$currentSettings = $this::get( $this->getId() );
		$isSame = $currentSettings === false || $this->settings === $currentSettings->settings;
		// save settings
		return update_option( 'gifttest_questionaire_' . (string) $this->getId() . '_settings', $this->settings, false ) || $isSame;
	}

	public function getId() {
		/*
		 * Get the questionaires' id
		 * 
		 * @return int: The questionaires' id
		 */
		return $this->getValue( 'id' );
	}
	
	public function getName() {
		/*
		 * Get the questionaires' name
		 * 
		 * @return string: The questionaires' name
		 */
		return $this->getValue( 'name' );
	}

	public function getValue( string $key ) {
		/*
		 * Get the questionaires' value for the given setting
		 * 
		 * @return mixed: The corresponding value
		 */
		// check if the key is valid
		if ( ! array_key_exists( $key, $this->settings ) ) throw new InvalidArgumentException( 'Invalid questionaire settings key "' . $key . '"' );
		return $this->settings[ $key ];
	}

	public static function create( string $name ) {
		/* 
		 * Create a new settings instance for the given Questionaire name
		 * 
		 * @param string $name: The questionaires' name
		 * @return Settings|false: A settings instance or false on error
		 */
		// create a new questionaire id
		$id = self::getCurrentMaxId() + 1;
		// generate data
		$data = [
			'id' => $id,
			'name' => $name,
			'shownTalentCount' => 5,
			'showMoreTalents' => true,
		];
		// reserver the generated questionaire id
		$idList = self::getQuestionaireIdList();
		$idList[] = $id;
		if ( ! self::updateQuestionaireIdList( $idList ) ) return false;
		// create settings instance
		return new Settings( $data );
	}

	public static function get( int $id ) {
		/*
		 * Get the settings for the given questionaire id
		 * 
		 * @param int $id: The questionaires' id
		 * @return Settings|false: A settings instance or false on error
		 */
		$settingsData = get_option( 'gifttest_questionaire_' . $id . '_settings', false );
		if ( $settingsData === false || ! is_array( $settingsData ) ) return false;
		// create settings instance
		return new Settings( $settingsData );
	}
	
	public function delete() {
		/*
		 * Delete the settings
		 * 
		 * @return bool: success or failure
		 */
		return delete_option( 'gifttest_questionaire_' . (string) $this->getId() . '_settings' );
	}

	public static function getQuestionaireIdList() {
		/*
		 * Get a list with all questionaire ids
		 * 
		 * @return array: A list of all questionaire ids
		 */
		return get_option( 'gifttest_questionaire_id_list', [] );
	}

	public static function updateQuestionaireIdList( array $idList ) {
		/*
		 * Update the questionaire id list
		 * 
		 * @param array $idList: The new questionaire id list
		 * @return bool: success or failure
		 */
		return update_option( 'gifttest_questionaire_id_list', $idList, false );
	}

	public static function getCurrentMaxId() {
		/*
		 * Get the highest questionaire id available right now
		 * 
		 * @return int: The highest questionaire id available right now
		 */
		$highestId = 0;
		$idList = get_option( 'gifttest_questionaire_id_list', [] );
		foreach ( $idList as $questionaireId ) {
			if ( $questionaireId > $highestId ) $highestId = $questionaireId;
		}
		return $highestId;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the questionaires' settings
		 * @return array
		 */
		foreach ( $this->settings as &$setting ) {
			$setting = esc_attr( $setting );
		}
		return $this->settings;
	}

	public static function jsonDeserialize( array $jsonObject ) {
		/*
		 * Create an instance from it's json representation
		 * 
		 * @param array $jsonObject: An Elements json representation
		 * @return IElement|false: An Element instance or false on error
		 */
		// make sure an id is given
		if ( ! isset( $jsonObject['id'] ) || ! is_numeric( $jsonObject['id'] ) ) return false;
		else return new Settings( $jsonObject );
	}
}
