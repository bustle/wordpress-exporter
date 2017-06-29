<?php
/**
 * Class Mobiledoc_Wrapper_Test
 *
 * @package Wordpress_Exporter
 */

/**
 * Base class to extend from
 */
class Mobiledoc_Wrapper_Test extends Bustle_Exporter_Test {

	/**
	 * Set up
	 */
	function setUp() {
		parent::setUp();
	}

	/**
	 * Get nodes
	 */
	function testGetNodes() {
		$wrapper = $this->get_mobiledoc_wrapper( 'Test post content' );
		$this->assertInstanceOf( 'DOMNodeList', $wrapper->get_nodes(), "DOM nodes aren't being retrieved properly." );
	}

	/**
	 * Get the markup indexes
	 */
	function testGetMarkupIndexes() {

		// Markup fragments -- we need to convert to nodes
		$markup_1 = '<strong>Sed mattis mi felis</strong>';
		$markup_2 = '<em>Quisque facilisis eleifend eleifend</em>';
		$markup_3 = '<a href="http://instagram.com/elitedaily" target="_blank">Cras vehicula tempus felis</a>';
		$markup_4 = '<strong>risus pharetra posuere</strong>';

		// Content with markups plugged in
		$content = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. {$markup_1}, id placerat mauris consequat et. Cras rhoncus vel velit a scelerisque.

		Duis non viverra libero, id sodales ipsum. {$markup_2}, nec imperdiet ex molestie vitae. Integer ac nisl at augue pharetra maximus non nec augue. Maecenas vitae nunc vel ipsum elementum pretium vel eget metus.

		Etiam molestie at ligula ut scelerisque. Integer leo odio, imperdiet vitae orci semper, tempus mollis dolor. In feugiat sodales pretium. {$markup_3}. Integer lacinia ante sit amet eros sollicitudin porttitor.

		Fusce ac arcu sed {$markup_4}. Donec bibendum enim ut nunc cursus ornare. Fusce vel hendrerit purus, et condimentum nisi. Phasellus eget quam vitae ante faucibus dignissim.

		Maecenas quis nisl convallis, convallis mi sit amet, luctus lectus.";

		$wrapper = $this->get_mobiledoc_wrapper( $content );

		// Reset the markups so we can assert each one individually
		$wrapper->markups = [];

		// Get corresponding nodes
		$node_1 = $wrapper->get_nodes( $markup_1 )->item( 0 )->childNodes->item( 0 );
		$node_2 = $wrapper->get_nodes( $markup_2 )->item( 0 )->childNodes->item( 0 );
		$node_3 = $wrapper->get_nodes( $markup_3 )->item( 0 )->childNodes->item( 0 );
		$node_4 = $wrapper->get_nodes( $markup_4 )->item( 0 )->childNodes->item( 0 );

		// Finally, the assertions
		$this->assertEquals( 0, $wrapper->get_markup_index( $node_1 ), 'Markup index is not being retrieved correctly.' );
		$this->assertEquals( 1, $wrapper->get_markup_index( $node_2 ), 'Multiple markup indexes are not being retrieved correctly.' );
		$this->assertEquals( 2, $wrapper->get_markup_index( $node_3 ), 'Unique markups with attributes are not being retrieved correctly.' );
		$this->assertEquals( 0, $wrapper->get_markup_index( $node_4 ), 'Repeat markups are not returning the correct markup index.' );
	}

	/**
	 * Test components filter
	 */
	function testGetComponents() {
		$wrapper = $this->get_mobiledoc_wrapper( 'Test components' );
		add_filter( 'bustle_mobiledoc_components', '__return_empty_array' );
		$this->assertEquals( [], $wrapper->get_components(), 'The components filter is not being applied correctly.' );
		remove_filter( 'bustle_mobiledoc_components', '__return_empty_array' );
	}

	/**
	 * Test card: YouTube
	 */
	function testGetCardYouTube() {
		$wrapper = $this->get_mobiledoc_wrapper( 'https://www.youtube.com/watch?v=OFBw35wrji0' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Embed', $wrapper->get_card( $node ), 'YouTube is not being recognized as an embed.' );
	}

	/**
	 * Test card: Instagram
	 */
	function testGetCardInstagram() {
		$wrapper = $this->get_mobiledoc_wrapper( 'https://www.instagram.com/p/BVQTC4qguy0/?taken-by=elitedaily' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Embed', $wrapper->get_card( $node ), 'Instagram is not being recognized as an embed.' );
	}

	/**
	 * Test card: Twitter
	 */
	function testGetCardTwitter() {
		$wrapper = $this->get_mobiledoc_wrapper( 'https://twitter.com/EliteDaily/status/874634967489191937' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Embed', $wrapper->get_card( $node ), 'Twitter is not being recognized as an embed.' );
	}

	/**
	 * Test card: <img>
	 */
	function testGetCardImg() {
		$wrapper = $this->get_mobiledoc_wrapper( '<img src="https://pbs.twimg.com/media/DCNU3aeUQAEuQGT.jpg" />' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Image', $wrapper->get_card( $node ), 'Image card is not being generated for <img> tags.' );
	}

	/**
	 * Test card: [caption]
	 */
	function testGetCardCaption() {
		$wrapper = $this->get_mobiledoc_wrapper( '[caption id="attachment_999" align="alignnone" width="733"]<img class="size-full wp-image-999" title="testimage" src="http://elitedaily.com/fun.jpg" alt="This is a caption" width="733" height="870" /> <span class="image-caption-wrapper">Netflix</span>[/caption]' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Image', $wrapper->get_card( $node ), 'Image card is not being generated for caption shortcode.' );
	}

	/**
	 * Test card: [caption]
	 */
	function testGetCardVideo() {
		$wrapper = $this->get_mobiledoc_wrapper( '[video src="http://elitedaily.com/movie.mp4" /]' );
		$node = $wrapper->get_nodes()->item( 0 );
		$this->assertInstanceOf( 'Bustle\Mobiledoc\Cards\Video', $wrapper->get_card( $node ), 'Video card is not being generated for video shortcode.' );
	}

	/**
	 * Tear down
	 */
	function tearDown() {
		parent::tearDown();
	}

}
