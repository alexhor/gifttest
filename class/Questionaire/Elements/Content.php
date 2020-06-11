<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Element.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementType.php' );


if ( ! class_exists( 'Content' ) ) :
	class Content extends Element {
		protected $type = ElementType::Content;

		function __construct( int $id, array $data ) {
			/*
			 * @param int $id: The elements id
			 * @param array $data: The elements data
			 */
			 // make sure default parameters are set
			$data = wp_parse_args( $data, [
				'text' => '',
			]);

			parent::__construct( $id, $data );
		}

		private function sanitizeData() {
			/*
			 * Sanitize this elements data
			 */
			$this->data['text'] = wp_kses_post( $this->data['text'] );
			foreach ( $this->data as $key => &$data ) {
				if ( $key === 'text' ) continue;
				$data = sanitize_text_field( $data );
			}
		}

		public function jsonSerialize() {
			/*
			 * Return a json serializeable representation of the content element
			 * @return array
			 */
			$this->sanitizeData();
			return [
				'id' => (int) $this->getId(),
				'type' => (int) $this->getType(),
				'data' => $this->data,
			];
		}
	}
endif;
