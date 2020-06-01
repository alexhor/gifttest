<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'IElement.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementFactory.php' );


abstract class Element implements IElement, \JsonSerializable {
	public $id;
	protected $type;
	protected $data;

	function __construct( int $id, array $data ) {
		/*
		 * @param int $id: The elements id
		 * @param array $data: The elements data
		 */
		$this->id = $id;
		$this->data = $data;
	}

	public function getId() {
		/*
		 * Get the elements id
		 * 
		 * @return int: The elements id
		 */
		return $this->id;
	}

	public function getType() {
		/*
		 * Get the elements type
		 * 
		 * @return int: The elements type
		 */
		return $this->type;
	}

	public function save( int $questionaireId ) {
		/*
		 * Save the elements to the database
		 * 
		 * @param int $questionaireId: The questionaires id to save this element for
		 * @return bool: success or failure
		 */
		// check if the element has changed
		$currentElement = ElementFactory::get( $questionaireId, $this->getId() );
		$isSame = $currentElement === false || $this->jsonSerialize() === $currentElement->jsonSerialize();
		// save element
		$data = [ 'data' => $this->data, 'type' => $this->getType() ];
		return update_option( 'gifttest_questionaire_' . (string) $questionaireId . '_element_' . (string) $this->getId(), $data, false ) || $isSame;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the element
		 * @return array
		 */
		foreach ( $this->data as &$data ) {
			$data = esc_html( $data );
		}

		return [
			'id' => esc_attr( $this->getId() ),
			'type' => esc_attr( $this->getType() ),
			'data' => $this->data,
		];
	}
}
