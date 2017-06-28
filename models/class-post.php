<?php

namespace Bustle;

/**
 * Class Post
 *
 * @package Bustle
 */
class Post extends Model {

	/**
	 * @var array
	 */
	protected $attributes = [
		'title'           => '',
		'clipTitle'       => '',
		'path'            => '',
		'slug'            => '',
		'type'            => '',
		'bodies'          => [],
		'rating'          => 'G',
		'nofollow'        => false,
		'primaryMediaURL' => '',
		'state'           => 'DRAFT',
		'updatedAt'       => 0,
		'publishedAt'     => 0,
		'createdAt'       => 0,
	];


	/**
	 * Post constructor.
	 *
	 * @param $post
	 */
	public function __construct( $post ) {

		$this->wordpress_model = $post;

		add_filter( 'bustle_exporter_post_attribute_bodies_sanitizer', '__return_false' );
		add_filter( 'bustle_exporter_post_attribute_title_sanitizer', '__return_false' );
		add_filter( 'bustle_exporter_post_attribute_updatedAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_publishedAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_createdAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_primaryMediaURL_sanitizer', 'esc_url' );

		if ( is_a( $post, '\WP_Post' ) ) {
			$this->convert_to_model( $post );
		}

	}


	/**
	 * Convert the default WordPress properties to a Bustle\Post model
	 *
	 * @param \WP_Post $post
	 *
	 * @return array
	 */
	private function convert_to_model( $post ) {

		$this->set_attribute( 'title', $post->post_title );
		$this->set_attribute( 'clipTitle', $post->post_excerpt );
		$this->set_attribute( 'path', str_replace( WP_HOME, '/', get_permalink( $post ) ) );
		$this->set_attribute( 'slug', $post->post_name );
		$this->set_attribute( 'type', $post->post_type );
		$this->set_attribute( 'bodies', [ new \Bustle\Mobiledoc\Wrapper( $post->ID ) ] );
		$this->set_attribute( 'primaryMediaURL', get_the_post_thumbnail_url( $post, 'full' ) );
		$this->set_attribute( 'state', $post->post_status );
		$this->set_attribute( 'updatedAt', strtotime( $post->post_modified_gmt ) * 1000 );
		$this->set_attribute( 'publishedAt', strtotime( $post->post_date_gmt ) * 1000 );
		$this->set_attribute( 'createdAt', strtotime( $post->post_date_gmt ) * 1000 );

		return $this->attributes;

	}

}
