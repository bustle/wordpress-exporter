<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Card
 *
 * Abstract card class
 *
 * @package Bustle\Mobiledoc
 */
abstract class Card {

	/**
	 * The card name
	 *
	 * @var string
	 */
	protected $name = '';

	/**
	 * The card payload
	 *
	 * @var array
	 */
	protected $payload = [];

	/**
	 * Set name and payload on construction
	 *
	 * @param \DOMElement $element The element to translate.
	 */
	public function __construct( $element ) {
		$this->name    = $this->_get_name( $element );
		$this->payload = $this->_get_payload( $element );
	}

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param \DOMElement $element The DOMElement to check.
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return false;
	}

	/**
	 * Get the name
	 *
	 * @param \DOMElement $element The element to translate.
	 *
	 * @return string The card name
	 */
	protected function _get_name( $element ) {
		return ( ! empty( $this->name ) ) ? $this->name : strtolower( get_class() );
	}

	/**
	 * Get the payload
	 *
	 * @param \DOMElement $element The element to get the payload for.
	 *
	 * @return array The card payload
	 */
	protected function _get_payload( $element ) {
		return [];
	}

	/**
	 * Get the card name
	 *
	 * @return string The card name
	 */
	public function get_name() {
		return $this->name;
	}

	/**
	 * Get the card payload
	 *
	 * @return string The card name
	 */
	public function get_payload() {
		$shortname = strtolower( basename( str_replace( '\\', '/', get_class( $this ) ) ) );
		return apply_filters( "bustle_mobiledoc_{$shortname}_payload", $this->payload );
	}

}
