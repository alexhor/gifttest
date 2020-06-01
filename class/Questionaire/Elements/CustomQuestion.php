<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( plugin_dir_path( __FILE__ ) . 'Element.php' );
require_once( plugin_dir_path( __FILE__ ) . 'ElementType.php' );


class CustomQuestion extends Element {
	protected $type = ElementType::CustomQuestion;

	function __construct( int $id, array $data ) {
		/*
		 * @param int $id: The elements id
		 * @param array $data: The elements data
		 */
		 // make sure default parameters are set
		$data = wp_parse_args( $data, [
			'questionText' => '',
			'answersList' => [],
		]);

		// populate answersList
		foreach( $data['answersList'] as $i => $answerData ) {
			if ( $answerData instanceof CustomQuestionAnswer ) continue;

			// id has to be set
			if ( ! isset( $answerData['id'] ) ) {
				unset( $data['answersList'][ $i ] );
				continue;
			}
			$answerId = $answerData['id'];
			unset( $answerData['id'] );
			$data['answersList'][ $i ] = new CustomQuestionAnswer( $answerId, $answerData );
		}

		parent::__construct( $id, $data );
	}

	public function getAnswer( int $answerId ) {
		/*
		 * Get an existign or create a new answer
		 * 
		 * @param int $questionaireId: The questionaires id this question and answer belong to
		 * @param int $questionId: The questions element id
		 * @param int $answerId: The answers id
		 * @return Answer: An Answer instance
		 */
		$requestedAnswer = false;
		// check if the answer exists
		foreach( $this->data['answersList'] as $answer ) {
			if ( $answer->getId() === $answerId ) {
				$requestedAnswer = $answer;
				break;
			}
		}
		
		// create a new answer if necessary
		if ( $requestedAnswer === false ) {
			$requestedAnswer = new CustomQuestionAnswer( $answerId, [] );
		}

		return $requestedAnswer;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the element
		 * @return array
		 */
		$data = $this->data;
		// serialize answers list
		foreach( $data['answersList'] as &$answer ) {
			$answer = $answer->jsonSerialize();
		}

		return [
			'id' => (int) esc_attr( $this->getId() ),
			'type' => (int) esc_attr( $this->getType() ),
			'data' => $data,
		];
	}
}

class CustomQuestionAnswer implements \JsonSerializable {
	protected $id;
	protected $data;

	function __construct( int $id, array $data ) {
		/*
		 * @param int $id: The answers id
		 * @param array $data: The answers data
		 */
		$this->id = $id;
		// make sure default parameters are set
		$this->data = wp_parse_args( $data, [
			'text' => '',
			'value' => [],
		]);
	}

	public function getId() {
		/*
		 * Get the answers id
		 * 
		 * @return int: The answers id
		 */
		return $this->id;
	}

	public function jsonSerialize() {
		/*
		 * Return a json serializeable representation of the element
		 * @return array
		 */
		return [
			'id' => (int) $this->getId(),
			'text' => $this->data['text'],
			'value' => $this->data['value'],
		];
	}
}
