<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Video
 *
 * Video embed card
 *
 * @package Bustle\Mobiledoc
 */
class Video extends Embed {

	/**
	 * The card name
	 *
	 * @var string
	 */
	protected $name = 'video-card';

	/**
	 * Set name and payload on construction
	 *
	 * @param int $video_id The video attachment ID
	 */
	public function __construct( $video_id ) {
		$this->payload = $this->_get_payload( $video_id );
	}

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param DOMElement $element The DOMElement to check
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( strpos( trim( $element->nodeValue ), '[video' ) === 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param DOMElement $element The element
	 */
	protected function _get_payload( $element ) {

		$payload = [];

		// Regex for matching video shortcode
		$pattern = get_shortcode_regex( [ 'video' ] );

		preg_match_all( '/'. $pattern .'/s', $element->nodeValue, $matches );

		// Look for source attribute and add to payload
		if ( isset( $matches[3][0] ) ) {
			$atts = shortcode_parse_atts( $matches[3][0] );
			if ( isset( $atts['src'] ) ) {
				$payload['url'] = $atts['src'];
			}
		}

		return $payload;
	}

}
