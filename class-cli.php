<?php

namespace Bustle;

/**
 * Bustle WordPress exporter commands for the WP-CLI framework
 *
 * @see https://github.com/wp-cli/wp-cli
 */
\WP_CLI::add_command( 'bustle-exporter', '\Bustle\Exporter_Command' );

class Exporter_Command extends \WP_CLI_Command {


	/**
	 * Export WordPress objects to Bustle
	 *
	 * ## DESCRIPTION
	 *
	 * Export JSON representations of WordPress objects (Posts, Terms, Users) for the Bustle CMS
	 *
	 * ## OPTIONS
	 *
	 *
	 * @synopsis <posts|terms|users> [--ids=<comma-delimited-objectIDs>] [--output=<stdout|file>]
	 *
	 * ## EXAMPLES
	 *
	 *     wp bustle-exporter export posts --ids 1234,1235
	 *     wp bustle-exporter export users --output=file
	 */
	public function export( $args, $assoc_args ) {

		$model = $args[0];

		if ( ! in_array( $model, [ 'posts', 'terms', 'users' ] ) ) {
			\WP_CLI::error( "'{$model}' is not an approved model." );
		}

		$output_format = \WP_CLI\Utils\get_flag_value( $assoc_args, 'output', 'stdout' );
		$ids           = str_getcsv( \WP_CLI\Utils\get_flag_value( $assoc_args, 'ids', '' ) );

		if ( ! in_array( $output_format, [ 'stdout', 'file' ] ) ) {
			\WP_CLI::error( "'{$output_format}' is not an approved output format." );
		}

		if ( $model === 'posts' ) {
			self::export_posts( $output_format, $ids );
		} elseif ( $model === 'terms' ) {

		} elseif ( $model === 'users' ) {
			self::export_users( $output_format, $ids );
		}

		if ( $output_format === 'file' ) {
			flock( self::get_log_file(), LOCK_UN );
			fclose( self::get_log_file() );
		}
	}

	/**
	 * @return bool|resource
	 */
	private static function get_log_file() {

		$file = fopen( 'bustle-export.json', 'w' );

		return $file;
	}


	/**
	 * @param string $output_format
	 * @param array $ids
	 */
	private static function export_posts( $output_format = 'stdout', $ids = [] ) {

		$posts_per_page = 100;
		$page           = 1;

		do {
			$query_args = array(
				'paged'          => $page,
				'posts_per_page' => $posts_per_page,
			);

			if ( ! empty( $ids ) ) {
				$query_args['posts__in'] = $ids;
			}

			$query = new \WP_Query( $query_args );

			if ( $query->have_posts() ) {

				foreach ( $query->posts as $post ) {
					$bustle_post = new \Bustle\Post( $post );

					$json_model = json_encode( $bustle_post->get_model() );

					if ( $output_format === 'stdout' ) {
						\WP_CLI::log( json_encode( $json_model ) );
					} elseif ( $output_format === 'file' ) {
						$file = self::get_log_file();
						fwrite( $file, $json_model );
					}

				}

			}

			$page ++;

		} while ( count( $query->posts ) );

	}


	/**
	 * @param string $output_format
	 * @param array $ids
	 */
	private static function export_users( $output_format = 'stdout', $ids = [] ) {

		$posts_per_page = 100;
		$page           = 1;

		do {
			$query_args = array(
				'paged'          => $page,
				'posts_per_page' => $posts_per_page,
			);

			if ( ! empty( $ids ) ) {
				$query_args['posts__in'] = $ids;
			}

			$users = new \WP_User_Query( $query_args );

			foreach ( $users->__get( 'results' ) as $user ) {
				$bustle_user = new \Bustle\User( $user );
				$json_model  = json_encode( $bustle_user->get_model() );

				if ( $output_format === 'stdout' ) {
					\WP_CLI::log( json_encode( $json_model ) );
				} elseif ( $output_format === 'file' ) {
					$file = self::get_log_file();
					fwrite( $file, $json_model );
				}
			}

			$page ++;

		} while ( count( $users->__get( 'results' ) ) );

	}
}
