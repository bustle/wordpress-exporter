<?php

namespace Bustle\Mobiledoc;

/**
 * Class Wrapper
 *
 * Wrapper for a Mobiledoc object
 *
 * @package Bustle\Mobiledoc
 */
class Wrapper {

	/**
	 * Mobiledoc version
	 *
	 * @var string
	 */
	public $version = '0.3.1';

	/**
	 * Markups
	 *
	 * @var array
	 */
	public $markups = [];

	/**
	 * Atoms
	 *
	 * @var array
	 */
	public $atoms = [];

	/**
	 * Cards
	 *
	 * @var array
	 */
	public $cards = [];

	/**
	 * Sections
	 *
	 * @var array
	 */
	public $sections = [];

	/**
	 * The post object
	 *
	 * @var \WP_Post
	 */
	protected $post = null;

	/**
	 * List of components to check for; order is
	 * important since that is the order we check matches
	 *
	 * @var array
	 */
	protected $components = [
		'\\Bustle\\Mobiledoc\\Cards\\Image',
		'\\Bustle\\Mobiledoc\\Cards\\Video',
		'\\Bustle\\Mobiledoc\\Cards\\Embed',
		'\\Bustle\\Mobiledoc\\Cards\\Iframe',
		'\\Bustle\\Mobiledoc\\Cards\\Object',
	];

	/**
	 * Constructor
	 *
	 * @param mixed $post The post ID or object.
	 */
	public function __construct( $post ) {

		// Get post object.
		if ( is_numeric( $post ) ) {
			$post = get_post( (int) $post );
		}

		// Post object check.
		if ( is_a( $post, 'WP_Post' ) ) {
			$this->post = $post;
			foreach ( $this->get_nodes() as $node ) {
				try {
					$this->handle_node( $node );
				} catch ( \Exception $e ) {
					return;
				}
			}
		}

	}

	/**
	 * Get DOMDocument nodes from random content
	 *
	 * @param string $content The content to translate.
	 *
	 * @return \DOMNodeList The nodes
	 */
	public function get_nodes( $content = '' ) {

		// If no string passed, fall back to WP_Post content
		if ( empty( $content ) && is_a( $this->post, 'WP_Post' ) ) {
			$content = $this->post->post_content;
		}

		// Wrap the content in <p> tags so its acceptable for DOMDocument.
		$content = wpautop( $content, false );

		// Use DOMDocument to parse HTML.
		$dom = new \DOMDocument();
		libxml_use_internal_errors( true );
		$dom->loadHTML( '<?xml encoding="utf-8" ?>' . $content );
		libxml_clear_errors( true );

		// Find the top-level nodes of the body tag.
		$nodes = $dom->getElementsByTagName( 'body' )->item( 0 )->childNodes;

		return $nodes;
	}

	/**
	 * Handle a single node
	 *
	 * @param $node \DOMElement $node The node.
	 *
	 * @throws \Exception If unsupported markup encountered.
	 */
	public function handle_node( $node ) {

		// We're only interesting in DOMElements with at least one child node.
		if ( ! is_a( $node, 'DOMElement' ) || $node->childNodes->length < 1 ) {
			return;
		}

		// Switch handling based on tag name.
		switch ( $node->tagName ) {

			case 'p' :
			case 'figure' : // Rare instance of figure elements can be handled like paragraphs.
				$this->handle_paragraph( $node );
				break;

			case 'h1' :
			case 'h2' :
			case 'h3' :
			case 'h4' :
			case 'h5' :
			case 'h6' :
				$this->handle_heading( $node );
				break;

			case 'ul' :
			case 'ol' :
				$this->handle_list( $node );
				break;

			case 'blockquote' :
				$this->handle_blockquote( $node );
				break;

			// Elements that can be returned early.
			case 'hr' :
			case 'noscript' :
			case 'style' :
				break;

			// Elements that need to be searched recursively.
			case 'div' :
			case 'dt' :
			case 'form' :
			case 'section' :
			case 'footer' :

				// Get inner HTML of node by imploding HTML of child elements.
				$inner_html = '';
				foreach ( $node->childNodes as $child_node ) {
					$inner_html .= $child_node->ownerDocument->saveHTML( $child_node );
				}

				// Trim so we can do an empty check.
				$inner_html = trim( $inner_html );

				// Transform result into DOMDocument nodes (w/wpautop).
				if ( ! empty( $inner_html ) ) {
					$child_nodes = $this->get_nodes( $inner_html );
					foreach ( $child_nodes as $child_node ) {
						$this->handle_node( $child_node );
					}
				}

				break;

			// Rare edge cases where wpautop fails to wrap inline styles in <p>.
			case 'em' :
			case 'strong' :
			case 'object' : // Objects can get this treatment too so we can check for object card.

				// Create new element and append the existing node to it.
				$new_paragraph = $node->ownerDocument->createElement( 'p' );
				$new_paragraph->appendChild( $node );

				// Handle like a normal paragraph.
				$this->handle_paragraph( $new_paragraph );

				break;

			default :
				throw new \Exception( "Unsupported top-level tag \e[1m{$node->tagName}\e[0m found.\n\e[47m" . $node->ownerDocument->saveHTML( $node ) . "\e[0m" );

		}// End switch().

	}

	/**
	 * Handling for a single paragraph
	 *
	 * @param \DOMElement $element The element.
	 */
	public function handle_paragraph( $element ) {

		// Determine if this node is a card.
		$card = $this->get_card( $element );

		// Create and add card, otherwise treat as regular paragraph.
		if ( false !== $card ) {

			$name    = $card->get_name();
			$payload = $card->get_payload();

			// If we didn't get a name or payload, something went wrong.
			if ( empty( $name ) || empty( $payload ) ) {
				return;
			}

			// Add to the cards array.
			$this->cards[] = [
				$name,
				$payload,
			];

			// Determine the new card index.
			end( $this->cards );
			$index = key( $this->cards );

			// Add the section.
			$this->sections[] = $this->get_section(
				10, // Type identifier for cards is always 10.
				$index
			);

		} else {

			// Maintain array of markers.
			$markers = [];

			foreach ( $element->childNodes as $node ) {
				$markers = array_merge( $markers, $this->get_markers( $node, [], 0 ) );
			}

			// Add section with populated markers array.
			if ( ! empty( $markers ) ) {
				$this->sections[] = $this->get_section(
					1,
					'p',
					$markers
				);
			}
		}// End if().

	}

	/**
	 * Get markers for a given node
	 *
	 * @param mixed $node Either a DOMText or a DOMElement.
	 * @param array $open_markups The markups that are already open.
	 * @param int   $open_tags The number of open tags.
	 *
	 * @return array An array of markers
	 */
	public function get_markers( $node, $open_markups = [], $open_tags = 0 ) {

		$markers = [];

		// Sanitize the node.
		$node = static::sanitize_node( $node );

		if ( is_a( $node, 'DOMElement' ) ) {

			// Get markup index and boost open tag counter.
			$open_markups[] = $this->get_markup_index( $node );
			$open_tags ++;

			// Check for nested elements.
			foreach ( $node->childNodes as $child_node ) {
				if ( is_a( $node, 'DOMText' ) ) {
					continue;
				} elseif ( is_a( $node, 'DOMElement' ) ) {
					$markers = array_merge( $markers, $this->get_markers( $child_node, $open_markups, $open_tags ) );
				}
			}
		} elseif ( is_a( $node, 'DOMText' ) ) {

			$markers[] = $this->get_marker(
				0,
				$open_markups,
				$open_tags,
				$node->nodeValue
			);

		}

		return $markers;
	}

	/**
	 * Roll all headings up into h2s
	 *
	 * @param \DOMElement $element The element.
	 */
	public function handle_heading( $element ) {

		// Get the markers.
		$markers = [];
		foreach ( $element->childNodes as $node ) {
			$markers = array_merge( $markers, $this->get_markers( $node, [], 0 ) );
		}

		// Add new section for the heading with a single marker.
		$this->sections[] = $this->get_section(
			1,
			'h2',
			$markers
		);

	}

	/**
	 * Handle <ol> and <ul>
	 *
	 * @param \DOMElement $element The element.
	 */
	public function handle_list( $element ) {

		// Maintain array of markers.
		$list_items = [];

		foreach ( $element->childNodes as $node ) {

			if ( ! is_a( $node, 'DOMElement' ) || 'li' !== $node->tagName ) {
				continue;
			}

			// Maintain array of markers.
			$markers = [];

			foreach ( $node->childNodes as $node ) {
				$markers = array_merge( $markers, $this->get_markers( $node, [], 0 ) );
			}

			$list_items[] = $markers;

		}

		// Add section with populated markers array.
		if ( ! empty( $list_items ) ) {
			$this->sections[] = $this->get_section(
				3,
				$element->tagName,
				$list_items
			);
		}

	}

	/**
	 * Handle blockquote
	 *
	 * @param \DOMElement $element The element.
	 */
	public function handle_blockquote( $element ) {

		// Add new section for the heading with a single marker.
		$this->sections[] = $this->get_section(
			1,
			'aside', // <blockquote> == <aside> in Typeset.
			[
				$this->get_marker(
					0,
					[],
					0,
					$element->nodeValue
				)
			]
		);

	}

	/**
	 * Get section data
	 *
	 * @param int    $section_type Mobiledoc section type identifier.
	 * @param string $tag_name The tag name.
	 * @param array  $markers The content markers.
	 *
	 * @return array The section data
	 */
	public function get_section( $section_type = 1, $tag_name = 'p', $markers = [] ) {
		return [
			$section_type,
			$tag_name,
			$markers,
		];
	}

	/**
	 * Get marker
	 *
	 * @param int    $text_type Mobiledoc text type identifier.
	 * @param array  $open_markups Indexes of open markups.
	 * @param int    $closed_markups The number of closed markups.
	 * @param string $value The actual value of the marker.
	 *
	 * @return array The marker data
	 */
	public function get_marker( $text_type = 0, $open_markups = [], $closed_markups = 0, $value = '' ) {
		sort( $open_markups );

		return [
			$text_type,
			$open_markups,
			$closed_markups,
			$value,
		];
	}

	/**
	 * Get the markup index
	 *
	 * @param \DOMElement $element The element to determine markup index for.
	 *
	 * @return int|null The index for the markup value or null if none determined
	 */
	public function get_markup_index( $element ) {

		// Default to null.
		$index = null;

		// Need DOMElement with a tag name.
		if ( is_a( $element, 'DOMElement' ) && ! empty( $element->tagName ) ) {

			$tag_name = $element->tagName;

			if ( $element->hasAttributes() ) {

				// Create array of attributes.
				$attributes = [];
				foreach ( $element->attributes as $attribute ) {
					$attributes[] = $attribute->name;
					$attributes[] = $attribute->value;
				}

				// Get the index.
				$this->markups[] = [ $tag_name, $attributes ];
				end( $this->markups );
				$index = key( $this->markups );

			} else {

				// Search registered markups.
				$search = false;
				foreach ( $this->markups as $key => $markup ) {
					if ( in_array( $tag_name, $markup, true ) ) {
						$search = $key;
						break;
					}
				}

				if ( ! is_int( $search ) ) {

					// No match found, register new markup and get index.
					$this->markups[] = [ $tag_name ];
					end( $this->markups );
					$index = key( $this->markups );

				} else {

					// Array search returns index, use that.
					$index = $search;

				}
			}// End if().
		}// End if().

		return $index;
	}

	/**
	 * Check if node is Mobiledoc-acceptable and return
	 * an alternative if it isn't
	 *
	 * @param object $element The object.
	 *
	 * @return object An object that is palatable to Mobiledoc
	 */
	public static function sanitize_node( $element ) {

		// Objects that need attention are DOMElement.
		if ( is_a( $element, 'DOMElement' ) ) {

			// Seek out very specific elements.
			switch ( $element->tagName ) {

				case 'span' :

					// <span> with the "s1" class indicates a copy-paste artifact
					// We don't need those artifacts, so just strip the elements
					// down to their text content.
					if ( $element->hasAttribute( 'class' ) && strpos( $element->getAttribute( 'class' ),
					's1' ) !== false
					) {
						$element = new \DOMText( $element->textContent );
					}

					break;

			}
		}

		return $element;
	}

	/**
	 * Determine if a DOMNode has a card
	 *
	 * @param \DOMElement $element The element.
	 *
	 * @return bool
	 */
	public function get_card( $element ) {

		$card = false;

		// Loop through components to find a card match
		if ( is_a( $element, 'DOMElement' ) ) {
			foreach ( $this->get_components() as $component ) {
				if ( $component::is_match( $element ) ) {
					$card = new $component( $element );
				}
			}
		}

		return $card;
	}

	/**
	 * Get components and apply a filter
	 *
	 * @return array
	 */
	public function get_components() {
		return apply_filters( 'bustle_mobiledoc_components', $this->components );
	}

}
