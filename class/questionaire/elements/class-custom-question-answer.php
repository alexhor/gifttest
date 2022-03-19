<?php
/**
 * File Name: class-custom-question-answer.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Custom_Question_Answer' ) ) :
	/**
	 * A possible answer for a custom question
	 *
	 * @since 1.0.0
	 */
	class Custom_Question_Answer implements \JsonSerializable {
		/**
		 * The answers id
		 *
		 * @var int
		 * @since 1.0.0
		 */
		protected $id;
		/**
		 * The answers data
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $data;

		/**
		 * Set default parameters
		 *
		 * @param int   $id The answers id.
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
					'text'  => '',
					'value' => array(),
				)
			);
		}

		/**
		 * Get the answers id
		 *
		 * @return int The answers id.
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Return a json serializeable representation of the element
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() : mixed {
			return array(
				'id'    => (int) sanitize_text_field( $this->get_id() ),
				'text'  => sanitize_text_field( $this->data['text'] ),
				'value' => sanitize_text_field( $this->data['value'] ),
			);
		}
	}
endif;
