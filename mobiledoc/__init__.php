<?php
require_once( dirname( __FILE__ ) . '/lib/class-wrapper.php' );
require_once( dirname( __FILE__ ) . '/lib/class-video-wrapper.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-card.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-embed.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-image.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-video.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-iframe.php' );
require_once( dirname( __FILE__ ) . '/lib/cards/class-object.php' );

// Skip fetching payloads on dev
if ( ! defined( 'PUBLIC_DOMAIN' ) || 'dev.elitedev.io' === PUBLIC_DOMAIN ) {
	add_filter( 'bustle_mobiledoc_embed_skip_payloads', '__return_true' );
}
