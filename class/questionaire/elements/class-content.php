<?php
/**
 * File Name: class-content.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-element.php';
require_once plugin_dir_path( __FILE__ ) . 'class-element-type.php';


if ( ! class_exists( 'Content' ) ) :
	/**
	 * An element displaying html text for a questionaires content
	 *
	 * @since 1.0.0
	 */
	class Content extends Element {
		/**
		 * This elements type
		 *
		 * @var Element_Type
		 * @since 1.0.0
		 */
		protected $type = Element_Type::Content;

		/**
		 * Set default parameters
		 *
		 * @param int   $id The elements id.
		 * @param array $data The elements data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			// Make sure default parameters are set.
			$data = wp_parse_args(
				$data,
				array(
					'text' => '',
				)
			);

			parent::__construct( $id, $data );
		}

		/**
		 * Sanitize this elements data
		 *
		 * @since 1.0.0
		 */
		private function sanitize_data() {
			$this->data['text'] = wp_kses_post( $this->data['text'] );
			foreach ( $this->data as $key => &$data ) {
				if ( 'text' === $key ) {
					continue;
				}
				$data = sanitize_text_field( $data );
			}
		}

		/**
		 * Return a json serializeable representation of the content element
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() : mixed {
			$this->sanitize_data();
			return array(
				'id'   => (int) $this->get_id(),
				'type' => (int) $this->get_type(),
				'data' => $this->data,
			);
		}
	}
endif;
