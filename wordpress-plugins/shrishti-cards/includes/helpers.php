<?php
/**
 * Shared helpers: company settings, card fields, lookups, phone formatting.
 */

defined( 'ABSPATH' ) || exit;

/** Company settings (Settings → Smart Cards), merged over defaults. Applies to every card. */
function shrishti_cards_company(): array {
	$defaults = array(
		'company_name'     => 'Shrishti Tours & Travels Pvt Ltd',
		'brand_name'       => 'Shrishti Tours & Travels',
		'header_line1'     => 'Tours & Travels',
		'header_line2'     => 'Est. 1997',
		'logo_id'          => 0,
		'office_phones'    => "+91-22-22053535\n+91-22-22053636",
		'email'            => 'info@shrishtiindia.com',
		'street'           => '315 Lotus House, New Marine Lines',
		'city'             => 'Mumbai',
		'region'           => 'Maharashtra',
		'zip'              => '400020',
		'country'          => 'India',
		'map_url'          => 'https://www.google.com/maps/search/?api=1&query=Lotus+House+New+Marine+Lines+Mumbai+400020',
		'hours'            => 'Mon–Fri 10:00–18:00 · Sat 10:00–16:00',
		'website'          => 'https://shrishtiindia.com',
		'group'            => "Shrishti Tours & Travels | Corporate travel, ticketing, visas, hotels | https://shrishtiindia.com\nAura Holiday | Curated Journeys · luxury holidays | https://aura.holiday\nCurated Affaires by Shrishti | Destination weddings & events | https://shrishtiindia.com/#curated-affairs",
		'credentials'      => '<b>IATA</b> accredited since 2006 · Member <b>TAFI · TAAI · IATO</b>',
		'vcard_note'       => 'IATA accredited travel agency since 2006. Corporate travel, luxury holidays (Aura Holiday), weddings and events (Curated Affaires by Shrishti).',
		'default_photo_id' => 0,
	);
	return wp_parse_args( (array) get_option( 'shrishti_cards_company', array() ), $defaults );
}

/** Per-card fields and their defaults. Stored as post meta `_sc_<key>`. */
function shrishti_cards_fields(): array {
	return array(
		'code'        => '',
		'active'      => '1',
		'first'       => '',
		'last'        => '',
		'designation' => '',
		'department'  => '',
		'photo_id'    => '0',
		'mobile'      => '',
		'whatsapp'    => '',
		'email'       => '',
		'linkedin'    => '',
		'wa_text'     => '',
		'languages'   => '',
		'hello'       => '1',
		'roles'       => '',
		'backdrop_l'  => '0',
		'backdrop_r'  => '0',
		'note'        => '',
	);
}

/** Other roles: "Role | Organisation | URL | Email" per line (URL and email optional). */
function shrishti_cards_parse_roles( string $text ): array {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $text ) as $line ) {
		$p = array_map( 'trim', explode( '|', $line ) );
		if ( count( $p ) >= 2 && '' !== $p[0] && '' !== $p[1] ) {
			$out[] = array(
				'role'  => $p[0],
				'org'   => $p[1],
				'url'   => isset( $p[2] ) ? esc_url_raw( $p[2], array( 'https', 'http' ) ) : '',
				'email' => isset( $p[3] ) ? sanitize_email( $p[3] ) : '',
			);
		}
	}
	return $out;
}

/** "9820052173" / "098200 52173" / "+91 98200-52173" → "+919820052173". Ten-digit numbers are taken as Indian. */
function shrishti_cards_e164( string $raw ): string {
	$d = preg_replace( '/\D+/', '', $raw );
	if ( '' === $d ) {
		return '';
	}
	if ( 10 === strlen( $d ) ) {
		$d = '91' . $d;
	} elseif ( 11 === strlen( $d ) && '0' === $d[0] ) {
		$d = '91' . substr( $d, 1 );
	}
	return '+' . $d;
}

/** "+919820052173" → "+91 98200 52173". */
function shrishti_cards_pretty_phone( string $e164 ): string {
	if ( preg_match( '/^\+91([6-9]\d{4})(\d{5})$/', $e164, $m ) ) {
		return '+91 ' . $m[1] . ' ' . $m[2];
	}
	return $e164;
}

/** Office phone lines from settings, as [ display, e164 ] pairs. */
function shrishti_cards_office_phones( array $co ): array {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $co['office_phones'] ) as $line ) {
		$line = trim( $line );
		if ( '' !== $line ) {
			$out[] = array( $line, shrishti_cards_e164( $line ) );
		}
	}
	return $out;
}

/** Group links from settings: "Name | Description | URL" per line. */
function shrishti_cards_group_links( array $co ): array {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $co['group'] ) as $line ) {
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) >= 3 && '' !== $parts[0] ) {
			$out[] = array(
				'name' => $parts[0],
				'desc' => $parts[1],
				'url'  => $parts[2],
			);
		}
	}
	return $out;
}

/** Find a card post by code, in any status except auto-draft. */
function shrishti_cards_find( string $code ): ?WP_Post {
	$posts = get_posts(
		array(
			'post_type'        => 'shrishti_card',
			'post_status'      => array( 'publish', 'draft', 'pending', 'private', 'future', 'trash' ),
			'meta_key'         => '_sc_code', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'       => $code, // phpcs:ignore WordPress.DB.SlowDBQuery
			'numberposts'      => 1,
			'no_found_rows'    => true,
			'suppress_filters' => true,
		)
	);
	return $posts ? $posts[0] : null;
}

function shrishti_cards_is_live( WP_Post $post ): bool {
	return 'publish' === $post->post_status && '1' === get_post_meta( $post->ID, '_sc_active', true );
}

/** Short address shown on the card, e.g. "shrishtiindia.com/gg007". */
function shrishti_cards_short_url( string $code ): string {
	$host = (string) wp_parse_url( home_url(), PHP_URL_HOST );
	$path = (string) wp_parse_url( home_url(), PHP_URL_PATH );
	return preg_replace( '/^www\./', '', $host ) . rtrim( $path, '/' ) . '/' . $code;
}

/** Everything a template needs about one card. */
function shrishti_cards_get( WP_Post $post ): array {
	$c = array();
	foreach ( shrishti_cards_fields() as $key => $default ) {
		$v         = get_post_meta( $post->ID, '_sc_' . $key, true );
		$c[ $key ] = ( '' === $v || false === $v ) ? $default : (string) $v;
	}
	$co              = shrishti_cards_company();
	$c['id']         = $post->ID;
	$c['name']       = trim( $c['first'] . ' ' . $c['last'] );
	$c['initials']   = strtoupper( mb_substr( $c['first'], 0, 1 ) . mb_substr( $c['last'], 0, 1 ) );
	$c['mobile_e164'] = shrishti_cards_e164( $c['mobile'] );
	$c['wa_e164']    = shrishti_cards_e164( '' !== $c['whatsapp'] ? $c['whatsapp'] : $c['mobile'] );
	$c['wa_digits']  = ltrim( $c['wa_e164'], '+' );
	$c['wa_separate'] = '' !== $c['whatsapp'] && $c['wa_e164'] !== $c['mobile_e164'];
	$c['wa_greeting'] = '' !== $c['wa_text'] ? $c['wa_text'] : sprintf( 'Hello %s, ', $c['first'] );
	$c['url']        = home_url( '/' . $c['code'] );
	$c['short']      = shrishti_cards_short_url( $c['code'] );
	$c['vcf_url']    = home_url( '/' . $c['code'] . '.vcf' );
	$c['png_url']    = home_url( '/' . $c['code'] . '.png' );
	$photo_id        = (int) $c['photo_id'] ? (int) $c['photo_id'] : (int) $co['default_photo_id'];
	$c['photo_att']  = $photo_id;
	$c['photo_url']  = $photo_id ? (string) wp_get_attachment_image_url( $photo_id, 'medium' ) : '';
	$c['og_image']   = $photo_id ? (string) wp_get_attachment_image_url( $photo_id, 'large' ) : '';
	$c['modified']   = $post->post_modified_gmt;
	$c['role_list']  = shrishti_cards_parse_roles( $c['roles'] );
	$c['bd_l_url']   = (int) $c['backdrop_l'] ? (string) wp_get_attachment_image_url( (int) $c['backdrop_l'], 'full' ) : '';
	$c['bd_r_url']   = (int) $c['backdrop_r'] ? (string) wp_get_attachment_image_url( (int) $c['backdrop_r'], 'full' ) : '';
	return $c;
}

/** Logo URL: chosen in settings, else the bundled cream script logo. */
function shrishti_cards_logo_url( array $co ): string {
	if ( (int) $co['logo_id'] ) {
		$url = wp_get_attachment_image_url( (int) $co['logo_id'], 'medium' );
		if ( $url ) {
			return $url;
		}
	}
	return SHRISHTI_CARDS_URL . 'assets/logo.png';
}

/** Purge LiteSpeed Cache for one card's addresses (no-op without LiteSpeed). */
function shrishti_cards_purge( string $code ): void {
	if ( '' === $code ) {
		return;
	}
	foreach ( array( '', '.vcf', '.png' ) as $suffix ) {
		do_action( 'litespeed_purge_url', home_url( '/' . $code . $suffix ) );
	}
}
