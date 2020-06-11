<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! interface_exists( 'IElement' ) ) :
	interface IElement {
		/*
		 * @param int $id: The elements id
		 * @param array $data: The elements data
		 */
		function __construct( int $id, array $data );

		/*
		 * Get the elements id
		 * 
		 * @return int: The elements id
		 */
		public function getId();

		/*
		 * Get the elements type
		 * 
		 * @return int: The elements type
		 */
		public function getType();

		/*
		 * Save the elements to the database
		 * 
		 * @param int $id: The questionaires id to save this element for
		 * @return bool: success or failure
		 */
		public function save( int $id );

		/*
		 * Return a json serializeable representation of the element
		 * @return array
		 */
		public function jsonSerialize();
	}
endif;
