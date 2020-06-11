<?php
/**
 * File Name: ielement.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! interface_exists( 'IElement' ) ) :
	interface IElement {
		/**
		 * Set default parameters
		 *
		 * @param int   $id The elements id.
		 * @param array $data The elements data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data );

		/**
		 * Get the elements id
		 *
		 * @return int The elements id.
		 *
		 * @since 1.0.0
		 */
		public function get_id();

		/**
		 * Get the elements type
		 *
		 * @return int The elements type.
		 *
		 * @since 1.0.0
		 */
		public function get_type();

		/**
		 * Save the elements to the database
		 *
		 * @param int $id The questionaires id to save this element for.
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save( int $id );
	}
endif;
