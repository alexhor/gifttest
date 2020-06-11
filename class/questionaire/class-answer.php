<?php
/**
 * File Name: class-answer.php
 *
 * @package gifttest
 *
 * @since 1.0.0
 */

namespace Gift_Test\Questionaire;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Answer' ) ) :
	/**
	 * An answer available for every normal question
	 *
	 * @since 1.0.0
	 */
	class Answer implements \JsonSerializable {
		const INT_VALS = array( 'value' );

		/**
		 * Set default parameters
		 *
		 * @param int   $id   The answers id.
		 * @param array $data The answers data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			$this->id = $id;
			// Make sure default parameters are set.
			$this->data = wp_parse_args(
				$data,
				array(
					'value'   => 0,
					'content' => '',
				)
			);

			foreach ( self::INT_VALS as $key ) {
				if ( isset( $this->data[ $key ] ) ) {
					$this->data[ $key ] = (int) $this->data[ $key ];
				}
			}
		}

		/**
		 * Get the answers id
		 *
		 * @return int The answers id
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get an existing answer of the given type
		 *
		 * @param int $questionaire_id The id of the questionaire this answer is associated with.
		 * @param int $id              The answers id in the database.
		 *
		 * @return Answer An answer instance.
		 *
		 * @since 1.0.0
		 */
		public static function get( int $questionaire_id, int $id ) {
			// Get answer data.
			$data = get_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_answer_' . (string) $id, array() );
			return new Answer( $id, $data );
		}

		/**
		 * Save the answer to the database
		 *
		 * @param int $questionaire_id The questionaires id to save this answer for.
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save( int $questionaire_id ) {
			// Sanitize data.
			$this->sanitize_data();

			// Check if the answer has changed.
			$current_answer = $this::get( $questionaire_id, $this->get_id() );
			$is_same        = $this->jsonSerialize() === $current_answer->jsonSerialize();

			// Save answer.
			return update_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_answer_' . (string) $this->get_id(), $this->data, false ) || $is_same;
		}

		/**
		 * Sanitize this answers data
		 *
		 * @since 1.0.0
		 */
		private function sanitize_data() {
			// Sanitize data.
			$this->id = sanitize_text_field( $this->id );
			foreach ( $this->data as &$data ) {
				$data = sanitize_text_field( $data );
			}

			// Handle int values.
			$this->id = (int) $this->id;
			foreach ( self::INT_VALS as $key ) {
				if ( isset( $this->data[ $key ] ) ) {
					$this->data[ $key ] = (int) $this->data[ $key ];
				}
			}
		}

		/**
		 * Return a json serializeable representation of the answer
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() {
			// Sanitize data.
			$this->sanitize_data();

			return array(
				'id'   => $this->get_id(),
				'data' => $this->data,
			);
		}

		/**
		 * Create an answer instance from it's json representation
		 *
		 * @param array $json_object An answers json representation.
		 *
		 * @return Answer|false An answers instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function json_deserialize( array $json_object ) {
			// Make sure an id is given.
			if ( ! isset( $json_object['id'] ) || ! is_numeric( $json_object['id'] ) ) {
				return false;
			}
			$id = $json_object['id'];
			unset( $json_object['id'] );

			if ( ! isset( $json_object['data'] ) ) {
				$json_object['data'] = array();
			}

			// Create answer instance.
			return new Answer( $id, $json_object['data'] );
		}
	}
endif;
