<?php

namespace Bustle;

/**
 * Class API
 *
 * @package Bustle
 */
class API {

	/**
	 * Register the WP JSON API endpoints
	 */
	public function __construct() {

		add_action( 'rest_api_init', function () {
			register_rest_route( 'bustle', '/posts/', [
				'methods'  => 'GET',
				'callback' => [ $this, 'posts_callback' ],
			] );
			register_rest_route( 'bustle', '/post/(?P<id>\d+)', [
				'methods'  => 'GET',
				'callback' => [ $this, 'post_callback' ],
			] );
			register_rest_route( 'bustle', '/user/(?P<id>\d+)', [
				'methods'  => 'GET',
				'callback' => [ $this, 'user_callback' ],
			] );
			register_rest_route( 'bustle', '/term/(?P<id>\d+)', [
				'methods'  => 'GET',
				'callback' => [ $this, 'term_callback' ],
			] );
		} );
	}

	/**
	 * Return a single WP_Post object
	 *
	 * @param object $request The request object.
	 *
	 * @return array|mixed|\WP_Error
	 */
	public function posts_callback( $request ) {

		$posts_json = [];

		$params         = $request->get_params();
		$posts_per_page = ( isset( $params['per_page'] ) ) ? (int) $params['per_page'] : 10;
		$page           = ( isset( $params['page'] ) ) ? (int) $params['page'] : 1;

		$query_args = [
			'posts_per_page' => $posts_per_page,
			'page'           => $page,
		];

		$posts = new \WP_Query( $query_args );

		if ( $posts->have_posts() ) {
			while ( $posts->have_posts() ) {
				$posts->the_post();

				$bustle_post  = new \Bustle\Post( get_post() );
				$posts_json[] = $bustle_post->get_model();
			}
		} else {
			return new \WP_Error( 'no_posts', 'No posts exist', [
				'status' => 404,
			] );
		}

		return $posts_json;
	}

	/**
	 * Return a single WP_Post object
	 *
	 * @param array $request The request object.
	 *
	 * @return array|mixed|\WP_Error
	 */
	public function post_callback( $request ) {
		$post_id = (int) $request['id'];

		$post = get_post( $post_id );

		if ( ! is_a( $post, 'WP_Post' ) ) {
			return new \WP_Error( 'no_post', "Post {$post_id} does not exist", [
				'status' => 404,
			] );
		}

		$bustle_post = new \Bustle\Post( $post );

		return $bustle_post->get_model();
	}

	/**
	 * Return a single WP_User object
	 *
	 * @param array $request The request object.
	 *
	 * @return array|mixed|\WP_Error
	 */
	public function user_callback( $request ) {
		$user_id = (int) $request['id'];

		$user = get_user_by( 'id', $user_id );

		if ( ! is_a( $user, 'WP_User' ) ) {
			return new \WP_Error( 'no_user', "User {$user_id} does not exist", [
				'status' => 404,
			] );
		}

		$bustle_user = new \Bustle\User( $user );

		return $bustle_user->get_model();
	}


	/**
	 * Return a single WP_Term object
	 *
	 * @param array $request The request object.
	 *
	 * @return array|mixed|\WP_Error
	 */
	public function term_callback( $request ) {
		$term_id = (int) $request['id'];

		$term = get_term( $term_id );

		if ( ! is_a( $term, 'WP_Term' ) ) {
			return new \WP_Error( 'no_term', "Term {$term_id} does not exist", [
				'status' => 404,
			] );
		}

		$bustle_term = new \Bustle\Term( $term );

		return $bustle_term->get_model();
	}
}


