<?php
namespace GiftTest\Questionaire;

if ( ! defined( 'ABSPATH' ) ) exit;


class Answer implements \JsonSerializable {
	function __construct( int $id, array $data ) {
		/*
		 * @param int $id: The answers id
		 * @param array $data: The answers data
		 */
		$this->id = $id;
		 // make sure default parameters are set
		$this->data = wp_parse_args( $data, [
			'value' => 0,
			'content' => '',
		]);
	}

	public function getId() {
		/*
		 * Get the answers id
		 * 
		 * @return int: The answers id
		 */
		return $this->id;
	}

	public static function get( int $questionaireId, int $id ) {
		/*
		 * Get an existing answer of the given type
		 * 
		 * @param int $questionaireId: The id of the questionaire this answer is associated with
		 * @param int $id: The answers id in the database
		 * @return Answer: An answer instance
		 */
		// get answer data
		$data = get_option( 'gifttest_questionaire_' . $questionaireId . '_answer_' . $id, [] );
		return new Answer( $id, $data );
	}

	public function save( int $questionaireId ) {
		/*
		 * Save the answer to the database
		 * 
		 * @param int $questionaireId: The questionaires id to save this answer for
		 * @return bool: success or failure
		 */
		// check if the answer has changed
		$currentAnswer = $this::get( $questionaireId, $this->getId() );
		$isSame = $this->jsonSerialize() === $currentAnswer->jsonSerialize();
		// save answer
		return update_option( 'gifttest_questionaire_' . (string) $questionaireId . '_answer_' . (string) $this->getId(), $this->data, false ) || $isSame;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the answer
		 * @return array
		 */
		$this->data['value'] = esc_attr( $this->data['value'] );
		$this->data['content'] = esc_html( $this->data['content'] );

		return [
			'id' => (int) esc_attr( $this->getId() ),
			'data' => $this->data,
		];
	}

	public static function jsonDeserialize( array $jsonObject ) {
		/*
		 * Create an answer instance from it's json representation
		 * 
		 * @param array $jsonObject: An answers json representation
		 * @return Answer|false: An answers instance or false on error
		 */
		// make sure an id is given
		if ( ! isset( $jsonObject['id'] ) || ! is_numeric( $jsonObject['id'] ) ) return false;
		$id = $jsonObject['id'];
		unset( $jsonObject['id'] );

		if ( ! isset( $jsonObject['data'] ) ) $jsonObject['data'] = [];

		// create answer instance
		return new Answer( $id, $jsonObject['data'] );
	}
}
