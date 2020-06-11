<?php
/**
 * File Name: class-questionaire.php
 *
 * @package gifttest
 *
 * @since 1.0.0
 */

namespace Gift_Test\Questionaire;

use Gift_Test\Questionaire\Element\Element_Factory;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'elements/class-element-factory.php';
require_once plugin_dir_path( __FILE__ ) . 'class-answer.php';
require_once plugin_dir_path( __FILE__ ) . 'class-gift.php';
require_once plugin_dir_path( __FILE__ ) . 'class-settings.php';


if ( ! class_exists( 'Questionaire' ) ) :
	/**
	 * A questionaire a user can answer get results from
	 *
	 * @since 1.0.0
	 */
	class Questionaire implements \JsonSerializable {
		/**
		 * A Settings object with the questionaires settings
		 *
		 * @var Settings
		 * @since 1.0.0
		 */
		protected $settings;
		/**
		 * A list of Element objects building the questionaires content
		 *
		 * @var array(Element)
		 * @since 1.0.0
		 */
		protected $element_list;
		/**
		 * A list of Answer objects for all questions to us
		 *
		 * @var array(Answer)
		 * @since 1.0.0
		 */
		protected $answer_list;
		/**
		 * A list of all gifts to show on the scoreboard.
		 *
		 * @var array(Gift)
		 * @since 1.0.0
		 */
		protected $gift_list;

		/**
		 * Set default data
		 *
		 * @param Settings       $settings A Settings object with the questionaires settings.
		 * @param array(Element) $element_list A list of Element objects building the questionaires content.
		 * @param array(Answer)  $answer_list A list of Answer objects for all questions to us.
		 * @param array(Gift)    $gift_list A list of all gifts to show on the scoreboard.
		 *
		 * @since 1.0.0
		 */
		public function __construct( Settings $settings, array $element_list = array(), array $answer_list = array(), array $gift_list = array() ) {
			$this->settings     = $settings;
			$this->element_list = $element_list;
			$this->answer_list  = $answer_list;
			$this->gift_list    = $gift_list;
		}

		/**
		 * Save the questionaire in its current state to the database
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save() {
			$success = true;
			// Save settings.
			if ( ! $this->settings->save() ) {
				return false;
			}

			/* save all other data */
			$data_to_save = array(
				'element' => $this->element_list,
				'answer'  => $this->answer_list,
				'gift'    => $this->gift_list,
			);
			foreach ( $data_to_save as $instance_name => $instance_list ) {
				$result_list = array();
				foreach ( $instance_list as $instance ) {
					$local_success = $instance->save( $this->get_id() );
					if ( $local_success ) {
						$result_list[] = $instance->get_id();
					}
				}
				// Check if the instance list has changed.
				$current_instance_list = get_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_' . $instance_name . '_list', array() );
				$is_same               = $current_instance_list === $result_list;
				// Save instance list.
				$success &= update_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_' . $instance_name . '_list', $result_list, false ) || $is_same;

				// Delete obsolete instances.
				foreach ( $current_instance_list as $instance_id ) {
					if ( ! in_array( $instance_id, $result_list, true ) ) {
						delete_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_' . $instance_name . '_' . (string) $instance_id );
					}
				}
			}

			return $success;
		}

		/**
		 * Get the questionaires' id
		 *
		 * @return int The questionaires' id.
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->settings->get_id( 'id' );
		}

		/**
		 * Get the questionaires' name
		 *
		 * @return string The questionaires' name.
		 *
		 * @since 1.0.0
		 */
		public function get_name() {
			return $this->settings->get_name();
		}

		/**
		 * Get an existing Questionaire instance
		 *
		 * @param int $questionaire_id The questionaires id in the database.
		 *
		 * @return Questionaire|false A Questionaire instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function get( int $questionaire_id ) {
			// Get questionaire settings.
			$settings = Settings::get( $questionaire_id );
			if ( false === $settings ) {
				return false;
			}

			/* Gather all instances from the database. */
			$instance_type_list = array( 'element', 'answer', 'gift' );
			foreach ( $instance_type_list as $instance_type ) {
				$instance_list    = array();
				$instance_id_list = get_option( 'gifttest_questionaire_' . (string) $questionaire_id . '_' . (string) $instance_type . '_list', array() );
				foreach ( $instance_id_list as $instance_id ) {
					// Create instance.
					switch ( $instance_type ) {
						case 'element':
							$instance = Element_Factory::get( $questionaire_id, $instance_id );
							break;
						case 'answer':
							$instance = Answer::get( $questionaire_id, $instance_id );
							break;
						case 'gift':
							$instance = Gift::get( $questionaire_id, $instance_id );
							break;
					}
					if ( false === $instance ) {
						continue;
					}
					// Add it to the data.
					$instance_list[] = $instance;
				}

				// Save fetched instances.
				switch ( $instance_type ) {
					case 'element':
						$element_list = $instance_list;
						break;
					case 'answer':
						$answer_list = $instance_list;
						break;
					case 'gift':
						$gift_list = $instance_list;
						break;
				}
			}

			// Generate the questionaire.
			return new Questionaire( $settings, $element_list, $answer_list, $gift_list );
		}

		/**
		 * Create a new questionaire instance
		 *
		 * @param string $name The questionaires name.
		 *
		 * @return Questionaire|false A Questionaire instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function create( string $name ) {
			// Generate new settings.
			$settings = Settings::create( $name );
			// Create a new questionaire from the settings and save it to the database.
			$questionaire = new Questionaire( $settings, array() );
			$questionaire->save();
			// Done.
			return $questionaire;
		}

		/**
		 * Return a json serializeable representation of the questionaire.
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() {
			return array(
				'settings'     => $this->settings,
				'element_list' => $this->element_list,
				'answer_list'  => $this->answer_list,
				'gift_list'    => $this->gift_list,
			);
		}

		/**
		 * Create an questionare instance from it's json representation
		 *
		 * @param array $json_object A questionaires json representation.
		 *
		 * @return Questionaire|false A Questionaire instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function json_deserialize( array $json_object ) {
			// Deserialize settings.
			if ( ! isset( $json_object['settings'] ) || ! is_array( $json_object['settings'] ) ) {
				return false;
			}
			$settings = Settings::json_deserialize( $json_object['settings'] );
			if ( false === $settings ) {
				return false;
			}

			// Deserialize elements.
			if ( ! isset( $json_object['element_list'] ) || ! is_array( $json_object['element_list'] ) ) {
				$json_object['element_list'] = array();
			}
			$element_list = array();
			foreach ( $json_object['element_list'] as $element_json_object ) {
				$element = Element_Factory::json_deserialize( $element_json_object );
				if ( false === $element ) {
					continue;
				} else {
					$element_list[] = $element;
				}
			}

			// Deserialize answers.
			if ( ! isset( $json_object['answer_list'] ) || ! is_array( $json_object['answer_list'] ) ) {
				$json_object['answer_list'] = array();
			}
			$answer_list = array();
			foreach ( $json_object['answer_list'] as $answer_json_object ) {
				$answer = Answer::json_deserialize( $answer_json_object );
				if ( false === $answer ) {
					continue;
				} else {
					$answer_list[] = $answer;
				}
			}

			// Deserialize gifts.
			if ( ! isset( $json_object['gift_list'] ) || ! is_array( $json_object['gift_list'] ) ) {
				$json_object['gift_list'] = array();
			}
			$gift_list = array();
			foreach ( $json_object['gift_list'] as $gift_json_object ) {
				$gift = Gift::json_deserialize( $gift_json_object );
				if ( false === $gift ) {
					continue;
				} else {
					$gift_list[] = $gift;
				}
			}

			// Generate questionaire instance.
			return new Questionaire( $settings, $element_list, $answer_list, $gift_list );
		}

		/**
		 * Delete this questionaire
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function delete() {
			$questionaire_id = $this->get_id();

			// Delete all instances from the database.
			$this->element_list = array();
			$this->answer_list  = array();
			$this->gift_list    = array();
			if ( ! $this->save() ) {
				return false;
			}

			// Delete all instance lists.
			foreach ( array( 'element', 'answer', 'gift' ) as $instance_type ) {
				delete_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_' . (string) $instance_type . '_list', array() );
			}

			// Remove the questionaires settings.
			if ( ! $this->settings->delete() ) {
				return false;
			}

			// Remove the questionaire itself.
			$id_list = Settings::get_questionaire_id_list();
			$key     = array_search( $questionaire_id, $id_list, true );
			if ( false !== $key ) {
				unset( $id_list[ $key ] );
			}
			return Settings::update_questionaire_id_list( $id_list );
		}
	}
endif;
