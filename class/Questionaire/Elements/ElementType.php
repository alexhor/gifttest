<?php
namespace GiftTest\Questionaire\Element;

if ( ! defined( 'ABSPATH' ) ) exit;


if ( ! class_exists( 'ElementType' ) ) :
	class ElementType {
		const Question = 0;
		const CustomQuestion = 2;
		const Content = 3;
	}
endif;
