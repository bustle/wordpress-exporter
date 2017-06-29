<?php
/**
 * Plugin Name: Bustle Exporter
 * Description: Register API routes and CLI commands for converting WordPress posts into Bustle objects.
 * Plugin URI: https://github.com/bustle/wordpress-exporter
 * Author: Bustle
 * Author URI: https://www.bustle.com
 * Version: 1.0.1
 * License: GPLv2 or later
 *
 * @package Bustle
 */

namespace Bustle;

/**
 * Class Exporter
 *
 * @package Bustle
 */
class Exporter {


	/**
	 * Initialize
	 */
	public static function init() {

		require_once( __DIR__ . '/models/class-model.php' );
		require_once( __DIR__ . '/models/class-user.php' );
		require_once( __DIR__ . '/models/class-term.php' );
		require_once( __DIR__ . '/models/class-post.php' );
		require_once( __DIR__ . '/class-api.php' );
		require_once( __DIR__ . '/mobiledoc/init.php' );

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
					header( 'Content-type: application/json' );
					echo wp_json_encode( $bustle_post->get_model() );
					exit();
				}
			} );

		}
	}

}

\Bustle\Exporter::init();


if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once( __DIR__ . '/class-cli.php' );
}
