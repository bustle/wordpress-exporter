<?php
/**
 * Class Mobiledoc_Image_Card_Test
 *
 * @package Wordpress_Exporter
 */

/**
 * Base class to extend from
 */
class Mobiledoc_Image_Card_Test extends Bustle_Exporter_Test {

	/**
	 * Set up
	 */
	function setUp() {
		parent::setUp();
	}

	/**
	 * Test caption shortcode
	 */
	function testIsMatchCaption() {
		$content = '[caption id="attachment_999" align="alignnone" width="733"]<img class="size-full wp-image-999" title="testimage" src="http://elitedaily.com/fun.jpg" alt="This is a caption" width="733" height="870" /> <span class="image-caption-wrapper">Netflix</span>[/caption]';
		$wrapper = $this->get_mobiledoc_wrapper( $content );
		$item = $wrapper->get_nodes()->item( 0 );
		$this->assertTrue( Bustle\Mobiledoc\Cards\Image::is_match( $item ), 'Captions are being recognized as image cards.' );
	}

	/**
	 * Test standalone image tag
	 */
	function testIsMatchImg() {
		$wrapper = $this->get_mobiledoc_wrapper( '<img src="http://elitedaily.com/fun.jpg" />' );
		$item = $wrapper->get_nodes()->item( 0 );
		$this->assertTrue( Bustle\Mobiledoc\Cards\Image::is_match( $item ), 'Standalone image tags are not being recognized as image cards.' );
	}

	/**
	 * Test non-images
	 */
	function testIsMatchFalse() {
		$wrapper = $this->get_mobiledoc_wrapper( 'This passage does not contain an image.' );
		$item = $wrapper->get_nodes()->item( 0 );
		$this->assertFalse( Bustle\Mobiledoc\Cards\Image::is_match( $item ), 'Non-images are incorrectly being determined as image cards.' );
	}

	/**
	 * Tear down
	 */
	function tearDown() {
		parent::tearDown();
	}

}
