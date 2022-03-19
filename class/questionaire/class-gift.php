<?php
/**
 * File Name: class-gift.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Gift' ) ) :
	/**
	 * A gift, shown on the scoreboard page
	 *
	 * @since 1.0.0
	 */
	class Gift implements \JsonSerializable {
		/**
		 * Set default parameters
		 *
		 * @param int   $id The gifts id.
		 * @param array $data The gifts data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			$this->id = $id;
			// Make sure default parameters are set.
			$this->data = wp_parse_args(
				$data,
				array(
					'title'         => '',
					'text'          => '',
					'question_list' => array(),
				)
			);
		}

		/**
		 * Sanitize this gifts data
		 *
		 * @since 1.0.0
		 */
		private function sanitize_data() {
			$this->data['title'] = sanitize_text_field( $this->data['title'] );
			$this->data['text']  = wp_kses_post( $this->data['text'] );
			foreach ( $this->data['question_list'] as &$question_id ) {
				$question_id = sanitize_text_field( $question_id );
			}
		}

		/**
		 * Get the gifts id
		 *
		 * @return int The gifts id.
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get an existing gift of the given type
		 *
		 * @param int $questionaire_id The id of the questionaire this gift is associated with.
		 * @param int $id The gifts id in the database.
		 *
		 * @return Gift An gift instance.
		 *
		 * @since 1.0.0
		 */
		public static function get( int $questionaire_id, int $id ) {
			// Get gift data.
			$data = get_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_gift_' . (string) $id, array() );
			return new Gift( $id, $data );
		}

		/**
		 * Save the gift to the database
		 *
		 * @param int $questionaire_id The questionaires id to save this gift for.
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save( int $questionaire_id ) {
			// Check if the answer has changed.
			$current_gift = $this::get( $questionaire_id, $this->get_id() );
			$is_same      = $this->jsonSerialize() === $current_gift->jsonSerialize();
			// Sanitize data.
			$this->sanitize_data();
			// Save gift.
			return update_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_gift_' . (string) $this->get_id(), $this->data, false ) || $is_same;
		}

		/**
		 * Return a json serializeable representation of the gift
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() : mixed {
			$this->sanitize_data();

			return array(
				'id'   => (int) $this->get_id(),
				'data' => $this->data,
			);
		}

		/**
		 * Create a gift instance from it's json representation
		 *
		 * @param array $json_object A gifts json representation.
		 *
		 * @return Gift|false A gifts instance or false on error.
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

			// Create gift instance.
			return new Gift( $id, $json_object['data'] );
		}
	}
endif;
