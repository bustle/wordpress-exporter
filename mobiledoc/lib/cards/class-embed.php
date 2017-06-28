<?php

namespace Bustle\Mobiledoc\Cards;

/**
 * Class Embed
 *
 * Generic embed card
 *
 * @package Bustle\Mobiledoc
 */
class Embed extends Card {

	/**
	 * The card name
	 *
	 * @var string
	 */
	protected $name = 'embed-card';

	/**
	 * Determines if DOMElement represents this card
	 *
	 * @param DOMElement $element The DOMElement to check
	 * @return bool If the element is this card or not
	 */
	public static function is_match( $element ) {
		return ( strpos( trim( $element->nodeValue ), 'http' ) === 0 );
	}

	/**
	 * Get the payload
	 *
	 * @param DOMElement $element The element translate
	 */
	protected function _get_payload( $element ) {

		$url = trim( $element->nodeValue );

		// Default payload
		$payload['url'] = $url;

		// Fetch payload from graphiql
		$maybe_payload = $this->fetch_payload( $url );
		if ( ! empty( $maybe_payload ) ) {
			$payload = $maybe_payload;
		} else {
			throw new \Exception( "Failed to fetch payload for \e[1m{$url}\e[0m." );
		}

		return $payload;
	}

	/**
	 * Get embed payload from Graphiql
	 *
	 * @param string $url The embed URL to query
	 * @return array The embed payload
	 */
	protected function fetch_payload( $url ) {

		// Filter for skipping payloads
		if ( apply_filters( 'bustle_mobiledoc_embed_skip_payloads', false ) ) {
			return [ 'skip_payload' => true ];
		}

		$num_retries = 6;

		$payload = [];
		$body = json_encode(
			[
				'query' => 'mutation ($input: FetchEmbedInput!) {
					fetchEmbed(input: $input) {
						embed {
							type
							key: mediaKey
							attribution_url: key
							attribution
							url: key
							provider_name: providerName
							attribution
							width
							height
							html
						}
					}
				}',
				'variables' => [
					'input' => [
						'url' => $url
					]
				]
			]
		);

		$count = 0;
		while( $count <= $num_retries ) {
			// Exponential backoff
			usleep( $count * $count * 500000);
			// Query Graphiql to get the payload
			$response = wp_remote_post( 'https://graph-staging.bustle.com/', [
				'headers' => [
					'authorization' => BUSTLE_USER . ':' . BUSTLE_PASS
				],
				'body' => $body,
				'timeout' => 20,
			] );
			$count++;
			if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
				continue;
			}

			// If we got the appropriate response, use that as payload
			$body = wp_remote_retrieve_body( $response );
			if ( empty( $body ) ) {
				continue;
			}
			$body = json_decode( $body );
			if ( !is_object( $body ) || empty( $body->data->fetchEmbed->embed ) ) {
				return $body->data->fetchEmbed->embed;
			}
		}
		throw new Exception( 'Failed to retrieve embed' );
	}

}