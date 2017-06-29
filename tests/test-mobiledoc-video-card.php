<?php
/**
 * Class Mobiledoc_Video_Card_Test
 *
 * @package Wordpress_Exporter
 */

/**
 * Base class to extend from
 */
class Mobiledoc_Video_Card_Test extends Bustle_Exporter_Test {

	/**
	 * Set up
	 */
	function setUp() {
		parent::setUp();
	}

	/**
	 * Test video shortcodes
	 */
	function testIsMatchShortcode() {
		$content = '[video src="http://elitedaily.com/movie.mp4" /]';
		$wrapper = $this->get_mobiledoc_wrapper( $content );
		$item = $wrapper->get_nodes()->item( 0 );
		$this->assertTrue( Bustle\Mobiledoc\Cards\Video::is_match( $item ), 'Video shortcodes are being recognized as video cards.' );
	}

	/**
	 * Test non-videos
	 */
	function testIsMatchFalse() {
		$wrapper = $this->get_mobiledoc_wrapper( 'This passage does not contain a video.' );
		$item = $wrapper->get_nodes()->item( 0 );
		$this->assertFalse( Bustle\Mobiledoc\Cards\Video::is_match( $item ), 'Non-videos are incorrectly being determined as video cards.' );
	}

	/**
	 * Tear down
	 */
	function tearDown() {
		parent::tearDown();
	}

}
