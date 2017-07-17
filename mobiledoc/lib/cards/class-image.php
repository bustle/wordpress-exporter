<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Image
 *
 * Image cards
 *
 * @package Bustle\Mobiledoc
 */
class Image extends Card {

	/**
	 * The card name
	 *
	 * @var string
	 */
	protected $name = 'image-card';

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param \DOMElement $element The DOMElement to check.
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( $element->getElementsByTagName( 'img' )->length > 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param \DOMElement $element The element translate.
	 *
	 * @return array
	 */
	protected function _get_payload( $element ) {

		$payload = [];

		// Get images (should be one).
		$imgs = $element->getElementsByTagName( 'img' );

		// Determine src and include other relevant properties for payload.
		if ( $imgs->length > 0 ) {

			// Get the first image and populate the payload.
			$img = $imgs->item( 0 );

			// Check for the source.
			if ( $img->hasAttribute( 'src' ) ) {
				$payload['url'] = $img->getAttribute( 'src' );
			}

			if ( $img->hasAttribute( 'height' ) ) {
				$payload['height'] = (int) $img->getAttribute( 'height' );
			}

			if ( $img->hasAttribute( 'width' ) ) {
				$payload['width'] = (int) $img->getAttribute( 'width' );
			}

			if ( isset( $payload['height'] ) && isset( $payload['height'] ) ) {

				$payload['ratio'] = 1;

				if ( $payload['width'] > 0 ) {
					$payload['ratio'] = $payload['height'] / $payload['width'];
				}	

				// Set orientation.
				$payload['orientation'] = 'landscape';
				if ( $payload['height'] > $payload['width'] ) {
					$payload['orientation'] = 'portrait';
				} elseif ( $payload['height'] === $payload['width'] ) {
					$payload['orientation'] = 'square';
				}
			}

			$payload['provider_name'] = 'upload';
			$payload['type'] = 'photo';
			$payload['_v'] = 1;

		}// End if().

		// Regex for matching video shortcode.
		$pattern = get_shortcode_regex( [ 'caption' ] );

		preg_match_all( '/' . $pattern . '/s', $element->textContent, $matches );

		// Extract content from shortcode match -- that will be the attribution.
		if ( isset( $matches[5][0] ) ) {
			$payload['attribution'] = $matches[5][0];
		}

		return $payload;
	}

}
