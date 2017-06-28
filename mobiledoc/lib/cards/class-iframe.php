<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Iframe
 *
 * Card for handling arbitrary iframes (i.e. Daily Mail video) 
 *
 * @package Bustle\Mobiledoc
 */
class Iframe extends Embed {

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param DOMElement $element The DOMElement to check
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( $element->getElementsByTagName( 'iframe' )->length > 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param DOMElement $element The element translate
	 */
	protected function _get_payload( $element ) {

		$iframe = $element->getElementsByTagName( 'iframe' )->item( 0 );

		// Payload just contains single property with iframe contents
		$payload['html'] = $element->ownerDocument->saveHTML( $iframe );

		return $payload;
	}

}
