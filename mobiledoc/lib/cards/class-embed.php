<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Embed
 *
 * Generic embed card
 *
 * @package Bustle\Mobiledoc
 */
class Embed extends Card {

	/**
	 * The card name
	 *
	 * @var string
	 */
	protected $name = 'embed-card';

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param DOMElement $element The DOMElement to check.
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( strpos( trim( $element->nodeValue ), 'http' ) === 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param DOMElement $element The element translate.
	 */
	protected function _get_payload( $element ) {

		$url = trim( $element->nodeValue );

		// Default payload.
		$payload['url'] = $url;

		return $payload;
	}

}
