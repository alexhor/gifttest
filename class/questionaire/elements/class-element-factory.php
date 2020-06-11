<?php
/**
 * File Name: class-element-factory.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-element-type.php';
require_once plugin_dir_path( __FILE__ ) . 'class-element.php';
require_once plugin_dir_path( __FILE__ ) . 'class-question.php';
require_once plugin_dir_path( __FILE__ ) . 'class-custom-question.php';
require_once plugin_dir_path( __FILE__ ) . 'class-content.php';


if ( ! class_exists( 'Element_Factory' ) ) :
	/**
	 * Factory for creating content elements for a questionaire
	 *
	 * @since 1.0.0
	 */
	class Element_Factory {
		/**
		 * Get an existing element
		 *
		 * @param int $questionaire_id The id of the questionaire this element is associated with.
		 * @param int $id The elements id in the database.
		 *
		 * @return Element|false An element instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function get( int $questionaire_id, int $id ) {
			// Get element data.
			$data       = get_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_element_' . (string) $id, array() );
			$data['id'] = $id;
			return self::json_deserialize( $data );
		}

		/**
		 * Creat a new element of the given type
		 *
		 * @param int $questionaire_id The id of the questionaire this element is associated with.
		 * @param int $id The elements id in the database.
		 * @param int $type Type of element.
		 *
		 * @return Element|false An element instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function create( int $questionaire_id, int $id, int $type ) {
			// Create the element.
			$element;
			switch ( $type ) {
				case Element_Type::Question:
					$element = new Question( $id, array() );
					break;
				case Element_Type::Custom_Question:
					$element = new Custom_Question( $id, array() );
					break;
				case Element_Type::Content:
					$element = new Content( $id, array() );
					break;
				default:
					// Invalid element type.
					return false;
			}
			// Return the element.
			return $element;
		}

		/**
		 * Create an element instance from it's json representation
		 *
		 * @param array $json_object An Elements json representation.
		 *
		 * @return IElement|false An Element instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function json_deserialize( array $json_object ) {
			if ( ! isset( $json_object['type'], $json_object['id'], $json_object['data'] ) ) {
				return false;
			}
			$id   = $json_object['id'];
			$type = $json_object['type'];

			// Create the element.
			$element;
			switch ( $type ) {
				case Element_Type::Question:
					$element = new Question( $id, $json_object['data'] );
					break;
				case Element_Type::Custom_Question:
					$element = new Custom_Question( $id, $json_object['data'] );
					break;
				case Element_Type::Content:
					$element = new Content( $id, $json_object['data'] );
					break;
				default:
					// Invalid element type.
					return false;
			}

			// Return the element.
			return $element;
		}
	}
endif;
