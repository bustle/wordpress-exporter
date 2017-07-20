<?php

namespace Bustle\Mobiledoc;

/**
 * Class Wrapper
 *
 * Wrapper for a Mobiledoc featured video object
 *
 * @package Bustle\Mobiledoc
 */
class Video_Wrapper extends Wrapper {

	/**
	 * Featured video is always going to have one
	 * section, which refers to the first (and
	 * only) card
	 *
	 * @var array
	 */
	public $sections = [];

	/**
	 * List of components to check for; order is
	 * important since that is the order we check matches
	 *
	 * @var array
	 */
	protected $components = [
		'\\Bustle\\Mobiledoc\\Cards\\Video'
	];

	/**
	 * Constructor
	 *
	 * @param string $video The featured video embed field.
	 */
	public function __construct( $video ) {

		// Transform into mini DOMDocument nodes.
		$nodes = $this->get_nodes( $video );

		if ( $nodes->length > 0 ) {

			$card = $this->get_card( $nodes->item( 0 ) );

			if ( false !== $card ) {

				// Set the single card
				$this->cards[] = [
					$card->get_name(),
					$card->get_payload(),
				];

				// Set the single section
				$this->sections[] = [
					10,
					0,
				];
			}
		}

	}

}
