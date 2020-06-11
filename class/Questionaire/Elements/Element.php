<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'IElement.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementFactory.php' );


if ( ! class_exists( 'Element' ) ) :
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

		private function sanitizeData() {
			/*
			 * Sanitize this settings data
			 */
			$this->id = (int) sanitize_text_field( $this->id );
			$this->type = (int) sanitize_text_field( $this->type );
			foreach ( $this->data as &$data ) {
				$data = sanitize_text_field( $data );
			}
		}

		public function jsonSerialize() {
			/*
			 * Return a json serializeable representation of the element
			 * @return array
			 */
			// sanitize data
			$this->sanitizeData();

			return [
				'id' => $this->getId(),
				'type' => $this->getType(),
				'data' => $this->data,
			];
		}
	}
endif;
