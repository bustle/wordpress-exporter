<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Object
 *
 * Card for handling object embeds
 *
 * @package Bustle\Mobiledoc
 */
class Object extends Embed {

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param \DOMElement $element The DOMElement to check.
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( $element->getElementsByTagName( 'object' )->length > 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param \DOMElement $element The element translate.
	 *
	 * @return array
	 */
	protected function _get_payload( $element ) {

		$object = $element->getElementsByTagName( 'object' )->item( 0 );

		// Payload just contains single property with iframe contents.
		$payload['html'] = $element->ownerDocument->saveHTML( $object );

		return $payload;
	}

}
