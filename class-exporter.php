<?php
/**
 * Plugin Name: Bustle Exporter
 * Description:
 * Plugin URI:
 * Author: Bustle
 * Author URI:
 * Version: 1.0.0
 * License: GPLv2 or later
 */

namespace Bustle;

/**
 * Class Exporter
 *
 * @package Bustle
 */
class Exporter {


	/**
	 *
	 */
	public static function init() {

		require_once( __DIR__ . '/models/class-model.php' );
		require_once( __DIR__ . '/models/class-user.php' );
		require_once( __DIR__ . '/models/class-term.php' );
		require_once( __DIR__ . '/models/class-post.php' );
		require_once( __DIR__ . '/class-api.php' );
		require_once( __DIR__ . '/mobiledoc/__init__.php' );

		$api_activated = apply_filters( 'bustle_exporter_api_active', true );

		if ( $api_activated ) {
			new \Bustle\API();
		}

		$endpoint_activated = apply_filters( 'bustle_exporter_endpoint_active', true );

		if ( $endpoint_activated ) {

			add_action( 'init', function () {
				add_rewrite_endpoint( 'bustle', EP_PERMALINK );
			}, 11 );

			add_action( 'template_redirect', function () {

				if ( get_query_var( 'bustle', false ) !== false ) {
					$bustle_post = new \Bustle\Post( get_post() );
					header( "Content-type: application/json" );
					echo json_encode( $bustle_post->get_model() );
					exit();
				}
			} );

		}
	}

}

add_action( 'init', function () {
	if ( \is_plugin_active( 'wordpress-exporter/class-exporter.php' ) ) {
		\Bustle\Exporter::init();
	}
}, 10 );


if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/class-cli.php' );
}
