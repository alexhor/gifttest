<?php
/**
 * File Name: class-settings.php
 *
 * @package gifttest
 *
 * @since 1.0.0
 */

namespace Gift_Test\Questionaire;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'Settings' ) ) :
	/**
	 * Settings for a questionaire
	 *
	 * @since 1.0.0
	 */
	class Settings implements \JsonSerializable {
		/**
		 * Internal settings store
		 *
		 * @var array
		 * @since 1.0.0
		 */
		protected $settings;
		const INT_VALS = array( 'id', 'shown_gift_count' );

		/**
		 * Set default parameters
		 *
		 * @param array $settings All settings for this questionaire.
		 *
		 * @since 1.0.0
		 */
		public function __construct( array $settings ) {
			// Set default data.
			$this->settings = wp_parse_args(
				$settings,
				array(
					'id'               => 0,
					'name'             => '',
					'shown_gift_count' => 5,
					'show_more_gifts'  => true,
				)
			);

			foreach ( self::INT_VALS as $key ) {
				if ( isset( $this->settings[ $key ] ) ) {
					$this->settings[ $key ] = (int) $this->settings[ $key ];
				}
			}
		}

		/**
		 * Save the questionaires' settings
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function save() {
			// Sanitize data.
			$this->sanitize_data();

			// Make sure the questionaire is registered.
			$id_list = $this::get_questionaire_id_list();
			if ( ! in_array( $this->get_id(), $id_list, true ) ) {
				$id_list[] = $this->get_id();
				if ( ! $this::update_questionaire_id_list( $id_list ) ) {
					return false;
				}
			}

			// Check if the settings have changed.
			$current_settings = $this::get( $this->get_id() );
			$is_same          = false === $current_settings || $this->settings === $current_settings->settings;
			// Save settings.
			return update_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_settings', $this->settings, false ) || $is_same;
		}

		/**
		 * Get the questionaires' id
		 *
		 * @return int The questionaires' id.
		 *
		 * @since 1.0.0
		 */
		public function get_id() {
			return $this->get_value( 'id' );
		}

		/**
		 * Get the questionaires' name
		 *
		 * @return string The questionaires' name.
		 *
		 * @since 1.0.0
		 */
		public function get_name() {
			return $this->get_value( 'name' );
		}

		/**
		 * Get the questionaires' value for the given setting
		 *
		 * @param string $key Which setting value to get.
		 *
		 * @throws InvalidArgumentException If the key doesn't exist.
		 * @return mixed The corresponding value.
		 *
		 * @since 1.0.0
		 */
		public function get_value( string $key ) {
			// Check if the key is valid.
			if ( ! array_key_exists( $key, $this->settings ) ) {
				throw new InvalidArgumentException( 'Invalid questionaire settings key "' . $key . '"' );
			}
			return $this->settings[ $key ];
		}

		/**
		 * Create a new settings instance for the given Questionaire name
		 *
		 * @param string $name The questionaires' name.
		 *
		 * @return Settings|false A settings instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function create( string $name ) {
			// Create a new questionaire id.
			$id = self::get_current_max_id() + 1;
			// Reserver the generated questionaire id.
			$id_list   = self::get_questionaire_id_list();
			$id_list[] = $id;
			if ( ! self::update_questionaire_id_list( $id_list ) ) {
				return false;
			}
			// Create settings instance.
			return new Settings(
				array(
					'id'   => $id,
					'name' => $name,
				)
			);
		}

		/**
		 * Get the settings for the given questionaire id
		 *
		 * @param int $id The questionaires' id.
		 *
		 * @return Settings|false A settings instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function get( int $id ) {
			$settings_data = get_option( 'gifttest_questionaire_' . (string) $id . '_settings', false );
			if ( false === $settings_data || ! is_array( $settings_data ) ) {
				return false;
			}
			// Create settings instance.
			return new Settings( $settings_data );
		}

		/**
		 * Delete the settings
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public function delete() {
			return delete_option( 'gifttest_questionaire_' . (string) $this->get_id() . '_settings' );
		}

		/**
		 * Get a list with all questionaire ids
		 *
		 * @return array A list of all questionaire ids.
		 *
		 * @since 1.0.0
		 */
		public static function get_questionaire_id_list() {
			return get_option( 'gifttest_questionaire_id_list', array() );
		}

		/**
		 * Update the questionaire id list
		 *
		 * @param array $id_list The new questionaire id list.
		 *
		 * @return bool Success or failure.
		 *
		 * @since 1.0.0
		 */
		public static function update_questionaire_id_list( array $id_list ) {
			return update_option( 'gifttest_questionaire_id_list', $id_list, false );
		}

		/**
		 * Get the highest questionaire id available right now
		 *
		 * @return int The highest questionaire id available right now.
		 *
		 * @since 1.0.0
		 */
		public static function get_current_max_id() {
			$highest_id = 0;
			$id_list    = get_option( 'gifttest_questionaire_id_list', array() );
			foreach ( $id_list as $questionaire_id ) {
				if ( $questionaire_id > $highest_id ) {
					$highest_id = $questionaire_id;
				}
			}
			return $highest_id;
		}

		/**
		 * Sanitize this settings data
		 *
		 * @since 1.0.0
		 */
		private function sanitize_data() {
			foreach ( $this->settings as &$setting ) {
				$setting = sanitize_text_field( $setting );
			}
			// Handle int values.
			foreach ( self::INT_VALS as $key ) {
				if ( isset( $this->settings[ $key ] ) ) {
					$this->settings[ $key ] = (int) $this->settings[ $key ];
				}
			}
		}

		/**
		 * Return a json serializeable representation of the questionaires' settings
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() {
			$this->sanitize_data();
			return $this->settings;
		}

		/**
		 * Create an instance from it's json representation
		 *
		 * @param array $json_object An Elements json representation.
		 *
		 * @return IElement|false An Element instance or false on error.
		 *
		 * @since 1.0.0
		 */
		public static function json_deserialize( array $json_object ) {
			// Make sure an id is given.
			if ( ! isset( $json_object['id'] ) || ! is_numeric( $json_object['id'] ) ) {
				return false;
			} else {
				return new Settings( $json_object );
			}
		}
	}
endif;
