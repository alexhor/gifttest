<?php
/**
 * File Name: class-element-type.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Element_Type' ) ) :
	/**
	 * Defines the different element types
	 *
	 * @since 1.0.0
	 */
	class Element_Type {
		// phpcs:ignore
		const Question        = 0;
		// phpcs:ignore
		const Custom_Question = 2;
		// phpcs:ignore
		const Content         = 3;
	}
endif;
