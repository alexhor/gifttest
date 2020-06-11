<?php
/**
 * File Name: class-custom-question.php
 *
 * @package gifttest
 */

namespace Gift_Test\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once plugin_dir_path( __FILE__ ) . 'class-element.php';
require_once plugin_dir_path( __FILE__ ) . 'class-element-type.php';
require_once plugin_dir_path( __FILE__ ) . 'class-custom-question-answer.php';


if ( ! class_exists( 'Custom_Question' ) ) :
	/**
	 * A question not using the answers provided by the questionaire
	 *
	 * @since 1.0.0
	 */
	class Custom_Question extends Element {
		/**
		 * This elements type
		 *
		 * @var Element_Type
		 * @since 1.0.0
		 */
		protected $type = Element_Type::Custom_Question;

		/**
		 * Set default parameters
		 *
		 * @param int   $id The questions id.
		 * @param array $data The questions data.
		 *
		 * @since 1.0.0
		 */
		public function __construct( int $id, array $data ) {
			// Make sure default parameters are set.
			$data = wp_parse_args(
				$data,
				array(
					'question_text' => '',
					'answers_list'  => array(),
				)
			);

			// Populate answers_list.
			foreach ( $data['answers_list'] as $i => $answer_data ) {
				if ( $answer_data instanceof Custom_Question_Answer ) {
					continue;
				}

				// Id has to be set.
				if ( ! isset( $answer_data['id'] ) ) {
					unset( $data['answers_list'][ $i ] );
					continue;
				}
				$answer_id = $answer_data['id'];
				unset( $answer_data['id'] );
				$data['answers_list'][ $i ] = new Custom_Question_Answer( $answer_id, $answer_data );
			}

			parent::__construct( $id, $data );
		}

		/**
		 * Get an existign or create a new answer
		 *
		 * @param int $answer_id The answers id.
		 *
		 * @return Answer An Answer instance.
		 *
		 * @since 1.0.0
		 */
		public function get_answer( int $answer_id ) {
			$requested_answer = false;
			// Check if the answer exists.
			foreach ( $this->data['answers_list'] as $answer ) {
				if ( $answer->get_id() === $answer_id ) {
					$requested_answer = $answer;
					break;
				}
			}

			// Create a new answer if necessary.
			if ( false === $requested_answer ) {
				$requested_answer = new Custom_Question_Answer( $answer_id, array() );
			}

			return $requested_answer;
		}

		/**
		 * Return a json serializeable representation of the element
		 *
		 * @return array
		 *
		 * @since 1.0.0
		 */
		public function jsonSerialize() {
			$data = $this->data;
			// Serialize answers list.
			foreach ( $data['answers_list'] as &$answer ) {
				$answer = $answer->jsonSerialize();
			}

			return array(
				'id'   => (int) sanitize_text_field( $this->get_id() ),
				'type' => (int) sanitize_text_field( $this->get_type() ),
				'data' => $data,
			);
		}
	}
endif;
