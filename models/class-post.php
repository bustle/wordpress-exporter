<?php

namespace Bustle;

/**
 * Class Post
 *
 * @see \Bustle\Model
 * @package Bustle
 */
class Post extends Model {

	/**
	 * The post attributes
	 *
	 * @var array
	 */
	protected $attributes = [
		'title'           => '',
		'clipTitle'       => '',
		'path'            => '',
		'slug'            => '',
		'type'            => '',
		'bodies'          => [],
		'rating'          => '',
		'nofollow'        => false,
		'primaryMediaPath' => '',
		'state'           => 'DRAFT',
		'updatedAt'       => 0,
		'publishedAt'     => 0,
		'createdAt'       => 0,
	];

	/**
	 * Dictionary to convert WP Post statuses
	 *
	 * @var array
	 */
	private static $post_status_mapping = [
		'publish'    => 'PUBLISHED',
		'draft'      => 'DRAFT',
		'auto-draft' => 'DRAFT',
		'future'     => 'DRAFT',
		'private'    => 'DRAFT',
	];


	/**
	 * Post constructor.
	 *
	 * @param \WP_Post $post The post object.
	 */
	public function __construct( $post ) {

		$this->wordpress_model = $post;

		// Update some of the sanitizers for Post attributes.
		add_filter( 'bustle_exporter_post_attribute_bodies_sanitizer', '__return_false' );
		add_filter( 'bustle_exporter_post_attribute_title_sanitizer', '__return_false' );
		add_filter( 'bustle_exporter_post_attribute_updatedAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_publishedAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_createdAt_sanitizer', 'intval' );
		add_filter( 'bustle_exporter_post_attribute_primaryMediaPath_sanitizer', 'esc_url' );

		if ( is_a( $post, '\WP_Post' ) ) {
			$this->convert_to_model( $post );
		}

	}


	/**
	 * Convert the default WordPress properties to a Bustle\Post model
	 *
	 * @param \WP_Post $post The post object.
	 *
	 * @return array
	 */
	private function convert_to_model( $post ) {

		$this->set_attribute( 'title', $post->post_title );
		$this->set_attribute( 'clipTitle', $post->post_excerpt );
		$this->set_attribute( 'path', str_replace( get_home_url(), '', get_permalink( $post ) ) );
		$this->set_attribute( 'slug', $post->post_name );
		$this->set_attribute( 'type', $post->post_type );
		$this->set_attribute( 'rating', 'G' );
		$this->set_attribute( 'nofollow', false );
		$this->set_attribute( 'bodies', [ new \Bustle\Mobiledoc\Wrapper( $post->ID ) ] );
		$this->set_attribute( 'primaryMediaPath', preg_replace( '#https?://[^/]+/#i', '', get_the_post_thumbnail_url( $post, 'full' ) ) );
		$this->set_attribute( 'state', self::$post_status_mapping[ $post->post_status ] );
		$this->set_attribute( 'updatedAt', strtotime( $post->post_modified_gmt ) * 1000 );
		$this->set_attribute( 'publishedAt', strtotime( $post->post_date_gmt ) * 1000 );
		$this->set_attribute( 'createdAt', strtotime( $post->post_date_gmt ) * 1000 );

		return $this->attributes;

	}

}
