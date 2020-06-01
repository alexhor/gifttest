<?php
namespace GiftTest\Questionaire;

if ( ! defined( 'ABSPATH' ) ) exit;


class Talent implements \JsonSerializable {
	function __construct( int $id, array $data ) {
		/*
		 * @param int $id: The talents id
		 * @param array $data: The talents data
		 */
		$this->id = $id;
		 // make sure default parameters are set
		$this->data = wp_parse_args( $data, [
			'title' => '',
			'text' => '',
			'questionList' => [],
		]);
	}

	private function sanitizeData() {
		/*
		 * Sanitize this elements data
		 */
		$this->data['text'] = wp_kses_post( $this->data['text'] );
		$this->data['title'] = esc_html( $this->data['title'] );
		foreach ( $this->data['questionList'] as &$questionId ) {
			$questionId = esc_attr( $questionId );
		}
	}

	public function getId() {
		/*
		 * Get the talents id
		 * 
		 * @return int: The talents id
		 */
		return $this->id;
	}

	public static function get( int $questionaireId, int $id ) {
		/*
		 * Get an existing talent of the given type
		 * 
		 * @param int $questionaireId: The id of the questionaire this talent is associated with
		 * @param int $id: The talents id in the database
		 * @return Talent: An talent instance
		 */
		// get talent data
		$data = get_option( 'gifttest_questionaire_' . $questionaireId . '_talent_' . $id, [] );
		return new Talent( $id, $data );
	}

	public function save( int $questionaireId ) {
		/*
		 * Save the talent to the database
		 * 
		 * @param int $questionaireId: The questionaires id to save this talent for
		 * @return bool: success or failure
		 */
		// check if the answer has changed
		$currentTalent = $this::get( $questionaireId, $this->getId() );
		$isSame = $this->jsonSerialize() === $currentTalent->jsonSerialize();
		// save talent
		return update_option( 'gifttest_questionaire_' . (string) $questionaireId . '_talent_' . (string) $this->getId(), $this->data, false ) || $isSame;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the talent
		 * @return array
		 */
		$this->sanitizeData();

		return [
			'id' => (int) esc_attr( $this->getId() ),
			'data' => $this->data,
		];
	}

	public static function jsonDeserialize( array $jsonObject ) {
		/*
		 * Create a talent instance from it's json representation
		 * 
		 * @param array $jsonObject: A talents json representation
		 * @return Talent|false: A talents instance or false on error
		 */
		// make sure an id is given
		if ( ! isset( $jsonObject['id'] ) || ! is_numeric( $jsonObject['id'] ) ) return false;
		$id = $jsonObject['id'];
		unset( $jsonObject['id'] );

		if ( ! isset( $jsonObject['data'] ) ) $jsonObject['data'] = [];

		// create talent instance
		return new Talent( $id, $jsonObject['data'] );
	}
}
