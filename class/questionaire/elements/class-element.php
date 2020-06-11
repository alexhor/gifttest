<?php
/**
 * File Name: class-element.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'IElement.php';
require_once plugin_dir_path( __FILE__ ) . 'class-element-factory.php';


if ( ! class_exists( 'Element' ) ) :
	/**
	 * An element from the questionaires content
	 *
	 * @since 1.0.0
	 */
	abstract class Element implements IElement, \JsonSerializable {
		/**
		 * The elements id
		 *
		 * @var int
		 * @since 1.0.0
		 */
		public $id;
		/**
		 * This elements type
		 *
		 * @var Element_Type
		 * @since 1.0.0
		 */
		protected $type;
		/**
		 * The elements data
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $data;

		/**
		 * Set default parameters
		 *
		 * @param int   $id The elements id.
		 * @param array $data The elements data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			$this->id   = $id;
			$this->data = $data;
		}

		/**
		 * Get the elements id
		 *
		 * @return int The elements id.
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->id;
		}

		/**
		 * Get the elements type
		 *
		 * @return int The elements type.
		 *
		 * @since 1.0.0
		 */
		public function get_type() {
			return $this->type;
		}

		/**
		 * Save the elements to the database
		 *
		 * @param int $questionaire_id The questionaires id to save this element for.
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save( int $questionaire_id ) {
			// Check if the element has changed.
			$current_element = Element_Factory::get( $questionaire_id, $this->get_id() );
			$is_same         = false === $current_element || $this->jsonSerialize() === $current_element->jsonSerialize();
			// Save element.
			$data = array(
				'data' => $this->data,
				'type' => $this->get_type(),
			);
			return update_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_element_' . (string) $this->get_id(), $data, false ) || $is_same;
		}

		/**
		 * Sanitize this settings data
		 *
		 * @since 1.0.0
		 */
		private function sanitize_data() {
			$this->id   = (int) sanitize_text_field( $this->id );
			$this->type = (int) sanitize_text_field( $this->type );
			foreach ( $this->data as &$data ) {
				$data = sanitize_text_field( $data );
			}
		}

		/**
		 * Return a json serializeable representation of the element
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
				'type' => $this->get_type(),
				'data' => $this->data,
			);
		}
	}
endif;
