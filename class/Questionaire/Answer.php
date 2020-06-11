<?php
namespace GiftTest\Questionaire;

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'Answer' ) ) :
	class Answer implements \JsonSerializable {
		const intVals = [ 'value' ];

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

			foreach ( self::intVals as $key ) {
				if ( isset ( $this->data[ $key ] ) ) $this->data[ $key ] = (int) $this->data[ $key ];
			}
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
			$data = get_option( 'gifttest_questionaire_' . (string) $questionaireId . '_answer_' . (string) $id, [] );
			return new Answer( $id, $data );
		}

		public function save( int $questionaireId ) {
			/*
			 * Save the answer to the database
			 * 
			 * @param int $questionaireId: The questionaires id to save this answer for
			 * @return bool: success or failure
			 */
			// sanitize data
			$this->sanitizeData();

			// check if the answer has changed
			$currentAnswer = $this::get( $questionaireId, $this->getId() );
			$isSame = $this->jsonSerialize() === $currentAnswer->jsonSerialize();

			// save answer
			return update_option( 'gifttest_questionaire_' . (string) $questionaireId . '_answer_' . (string) $this->getId(), $this->data, false ) || $isSame;
		}

		private function sanitizeData() {
			/*
			 * Sanitize this answers data
			 */
			// sanitize data
			$this->id = sanitize_text_field( $this->id );
			foreach ( $this->data as &$data ) {
				$data = sanitize_text_field( $data );
			}

			// handle int values
			$this->id = (int) $this->id;
			foreach ( self::intVals as $key ) {
				if ( isset ( $this->data[ $key ] ) ) $this->data[ $key ] = (int) $this->data[ $key ];
			}
		}

		public function jsonSerialize() {
			/*
			 * Return a json serializeable representation of the answer
			 * @return array
			 */
			// sanitize data
			$this->sanitizeData();

			return [
				'id' => $this->getId(),
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
endif;
