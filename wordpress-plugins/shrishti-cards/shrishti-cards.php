<?php
/**
 * Plugin Name:       Shrishti Smart Cards
 * Description:       Personal online visiting cards for the Shrishti team at shrishtiindia.com/&lt;code&gt;, with Save-to-contacts (vCard) and QR codes. Cards are unlisted and marked noindex.
 * Version:           1.0.0
 * Requires at least: 6.5
 * Requires PHP:      8.1
 * Author:            Shrishti Tours & Travels
 * License:           GPL-2.0-or-later
 * Text Domain:       shrishti-cards
 *
 * Build spec: docs/visiting-cards/BUILD_SPEC.md in the shrishtiindia-website repository.
 */

defined( 'ABSPATH' ) || exit;

define( 'SHRISHTI_CARDS_VERSION', '1.0.0' );
define( 'SHRISHTI_CARDS_FILE', __FILE__ );
define( 'SHRISHTI_CARDS_DIR', plugin_dir_path( __FILE__ ) );
define( 'SHRISHTI_CARDS_URL', plugin_dir_url( __FILE__ ) );
define( 'SHRISHTI_CARDS_CODE_RE', '[a-z]{2}[0-9]{3}' );

/* Bundled QR library (chillerlan/php-qrcode 5.0.5 + php-settings-container 3.3.0, MIT/Apache-2.0). */
spl_autoload_register(
	function ( $class ) {
		$map = array(
			'chillerlan\\QRCode\\'   => 'vendor/chillerlan/php-qrcode/src/',
			'chillerlan\\Settings\\' => 'vendor/chillerlan/php-settings-container/src/',
		);
		foreach ( $map as $prefix => $dir ) {
			if ( 0 === strncmp( $class, $prefix, strlen( $prefix ) ) ) {
				$file = SHRISHTI_CARDS_DIR . $dir . str_replace( '\\', '/', substr( $class, strlen( $prefix ) ) ) . '.php';
				if ( is_readable( $file ) ) {
					require $file;
				}
				return;
			}
		}
	}
);

require SHRISHTI_CARDS_DIR . 'includes/helpers.php';
require SHRISHTI_CARDS_DIR . 'includes/qr.php';
require SHRISHTI_CARDS_DIR . 'includes/vcard.php';
require SHRISHTI_CARDS_DIR . 'includes/front.php';
require SHRISHTI_CARDS_DIR . 'includes/admin.php';
require SHRISHTI_CARDS_DIR . 'includes/settings.php';

register_activation_hook(
	__FILE__,
	function () {
		shrishti_cards_register_post_type();
		shrishti_cards_add_rewrites();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	function () {
		// Rules are rebuilt on the next request without this plugin's rules.
		delete_option( 'rewrite_rules' );
	}
);
