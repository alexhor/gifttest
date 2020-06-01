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
		$data['text'] = wp_kses_post( $data['text'] );
		parent::__construct( $id, $data );
	}
}
