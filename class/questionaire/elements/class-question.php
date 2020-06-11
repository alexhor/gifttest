<?php
/**
 * File Name: class-question.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-element.php';
require_once plugin_dir_path( __FILE__ ) . 'class-element-type.php';


if ( ! class_exists( 'Question' ) ) :
	/**
	 * A normal question inside a questionaire
	 *
	 * @since 1.0.0
	 */
	class Question extends Element {
		/**
		 * This elements type
		 *
		 * @var Element_Type
		 * @since 1.0.0
		 */
		protected $type = Element_Type::Question;

		/**
		 * Set default parameters
		 *
		 * @param int   $id The questions id.
		 * @param array $data The questions data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			/*
			 * @param int $id: The elements id
			 * @param array $data: The elements data
			 */
			// Make sure default parameters are set.
			$data = wp_parse_args(
				$data,
				array(
					'text' => '',
				)
			);
			parent::__construct( $id, $data );
		}
	}
endif;
