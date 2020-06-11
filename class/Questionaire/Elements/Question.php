<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Element.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementType.php' );


if ( ! class_exists( 'Question' ) ) :
	class Question extends Element {
		protected $type = ElementType::Question;

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
	}
endif;
