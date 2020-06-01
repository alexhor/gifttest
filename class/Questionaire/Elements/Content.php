<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Element.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementType.php' );


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
		$this->sanitizeData();
		parent::__construct( $id, $data );
	}

	private function sanitizeData() {
		/*
		 * Sanitize this elements data
		 */
		$this->data['text'] = wp_kses_post( $this->data['text'] );
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the content element
		 * @return array
		 */
		$this->sanitizeData();
		return [
			'id' => (int) esc_attr( $this->getId() ),
			'type' => (int) esc_attr( $this->getType() ),
			'data' => $this->data,
		];
	}
}
