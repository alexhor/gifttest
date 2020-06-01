<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'ElementType.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Element.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Question.php' );
require_once( plugin_dir_path( __FILE__ ) . 'CustomQuestion.php' );
require_once( plugin_dir_path( __FILE__ ) . 'Content.php' );


class ElementFactory {
	public static function get( int $questionaireId, int $id ) {
		/*
		 * Get an existing element
		 * 
		 * @param int $questionaireId: The id of the questionaire this element is associated with
		 * @param int $id: The elements id in the database
		 * @return Element|false: An element instance or false on error
		 */
		// get element data
		$data = get_option( 'gifttest_questionaire_' . (string) $questionaireId . '_element_' . (string) $id, [] );
		$data['id'] = $id;
		return self::jsonDeserialize( $data );
	}

	public static function create( int $questionaireId, int $id, int $type ) {
		/*
		 * creat a new element of the given type
		 * 
		 * @param int $questionaireId: The id of the questionaire this element is associated with
		 * @param int $id: The elements id in the database
		 * @param ElementType(int) $type: Type of element
		 * @return Element|false: An element instance or false on error
		 */
		// create the element
		$element;
		switch ( $type ) {
			case ElementType::Question:
				$element = new Question( $id, [] );
				break;
			case ElementType::CustomQuestion:
				$element = new CustomQuestion( $id, [] );
				break;
			case ElementType::Content:
				$element = new Content( $id, [] );
				break;
			default:
				// invalid element type
				return false;
		}
		// return the element
		return $element;
	}
	
	public static function jsonDeserialize( array $jsonObject ) {
		/*
		 * Create an element instance from it's json representation
		 * 
		 * @param array $jsonObject: An Elements json representation
		 * @return IElement|false: An Element instance or false on error
		 */
		if ( ! isset( $jsonObject['type'], $jsonObject['id'], $jsonObject['data'] ) ) return false;
		$id = $jsonObject['id'];
		$type = $jsonObject['type'];

		// create the element
		$element;
		switch ( $type ) {
			case ElementType::Question:
				$element = new Question( $id, $jsonObject['data'] );
				break;
			case ElementType::CustomQuestion:
				$element = new CustomQuestion( $id, $jsonObject['data'] );
				break;
			case ElementType::Content:
				$element = new Content( $id, $jsonObject['data'] );
				break;
			default:
				// invalid element type
				return false;
		}

		// return the element
		return $element;
	}
}
