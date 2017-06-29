<?php
/**
 * Class Bustle_Exporter_Test
 *
 * @package Wordpress_Exporter
 */

/**
 * Base class to extend from
 */
class Bustle_Exporter_Test extends WP_UnitTestCase {

	/**
	 * Helper for getting a Mobiledoc wrapper
	 *
	 * @param string $content The content to create the wrapper from
	 */
	function get_mobiledoc_wrapper( $content ) {
		$post = $this->factory->post->create_and_get( [
			'post_title'   => 'Test Title ' . md5( $content ),
			'post_content' => $content
		] );
		return new \Bustle\Mobiledoc\Wrapper( $post );
	}

}
