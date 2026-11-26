<?php
/**
 * Public addresses: /<code>, /<code>.vcf, /<code>.png (spec §2, §3, §8).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'shrishti_cards_add_rewrites' );

function shrishti_cards_add_rewrites(): void {
	// Mixed case is matched so /GG007 can be redirected to /gg007.
	add_rewrite_rule( '^([A-Za-z]{2}[0-9]{3})\.(vcf|png)$', 'index.php?sc_card=$matches[1]&sc_fmt=$matches[2]', 'top' );
	add_rewrite_rule( '^([A-Za-z]{2}[0-9]{3})/?$', 'index.php?sc_card=$matches[1]', 'top' );
}

add_filter(
	'query_vars',
	function ( $vars ) {
		$vars[] = 'sc_card';
		$vars[] = 'sc_fmt';
		return $vars;
	}
);

/* Reserve the code pattern: no page or post may take a slug like "ab123". */
add_filter(
	'wp_unique_post_slug',
	function ( $slug, $post_id, $status, $type ) {
		if ( 'shrishti_card' !== $type && preg_match( '/^' . SHRISHTI_CARDS_CODE_RE . '$/', $slug ) ) {
			return $slug . '-page';
		}
		return $slug;
	},
	10,
	4
);

add_action(
	'parse_request',
	function ( $wp ) {
		if ( empty( $wp->query_vars['sc_card'] ) ) {
			return;
		}
		shrishti_cards_serve( (string) $wp->query_vars['sc_card'], (string) ( $wp->query_vars['sc_fmt'] ?? '' ) );
		exit;
	},
	0
);

function shrishti_cards_noindex(): void {
	header( 'X-Robots-Tag: noindex, nofollow, noarchive', true );
}

function shrishti_cards_no_cache(): void {
	nocache_headers();
	header( 'X-LiteSpeed-Cache-Control: no-cache', true );
	do_action( 'litespeed_control_set_nocache', 'shrishti smart card' );
}

function shrishti_cards_serve( string $raw, string $fmt ): void {
	$code = strtolower( $raw );
	$fmt  = in_array( $fmt, array( 'vcf', 'png' ), true ) ? $fmt : '';

	if ( $code !== $raw ) {
		wp_safe_redirect( home_url( '/' . $code . ( $fmt ? '.' . $fmt : '' ) ), 301 );
		exit;
	}

	shrishti_cards_noindex();
	$post = shrishti_cards_find( $code );
	$live = $post && shrishti_cards_is_live( $post );

	// Editors can preview an inactive or draft card with ?preview=1.
	$preview = ! $live && $post && '' === $fmt && isset( $_GET['preview'] ) && current_user_can( 'edit_post', $post->ID ); // phpcs:ignore WordPress.Security.NonceVerification

	if ( ! $live && ! $preview ) {
		$status = $post ? 410 : 404;
		status_header( $status );
		shrishti_cards_no_cache();
		if ( $fmt ) {
			header( 'Content-Type: text/plain; charset=utf-8' );
			echo 410 === $status ? 'This card is no longer active.' : 'Not found.';
			return;
		}
		$co = shrishti_cards_company();
		require SHRISHTI_CARDS_DIR . 'templates/closed.php';
		return;
	}

	$c  = shrishti_cards_get( $post );
	$co = shrishti_cards_company();

	if ( 'vcf' === $fmt ) {
		$filename = sanitize_file_name( sanitize_title( $c['first'] . '-' . $c['last'] ) . '-shrishti.vcf' );
		status_header( 200 );
		shrishti_cards_no_cache();
		header( 'Content-Type: text/vcard; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
		echo shrishti_cards_vcard( $c, $co ); // phpcs:ignore WordPress.Security.EscapeOutput -- vCard text, not HTML.
		if ( 'GET' === ( $_SERVER['REQUEST_METHOD'] ?? '' ) ) {
			// Privacy-friendly counter: a number only, no cookies, no IPs.
			update_post_meta( $post->ID, '_sc_saves', (int) get_post_meta( $post->ID, '_sc_saves', true ) + 1 );
		}
		return;
	}

	if ( 'png' === $fmt ) {
		$png = shrishti_cards_qr_png( $c['url'], 512 );
		if ( '' === $png ) {
			status_header( 501 );
			return;
		}
		status_header( 200 );
		header( 'Content-Type: image/png' );
		header( 'Cache-Control: public, max-age=86400' );
		echo $png; // phpcs:ignore WordPress.Security.EscapeOutput -- binary image.
		return;
	}

	status_header( 200 );
	header( 'Content-Type: text/html; charset=utf-8' );
	if ( $preview ) {
		shrishti_cards_no_cache();
	} else {
		header( 'Cache-Control: public, max-age=300' );
	}
	require SHRISHTI_CARDS_DIR . 'templates/card.php';
}

/** Inline @font-face for the theme's self-hosted Latin fonts (falls back to system fonts if the theme is absent). */
function shrishti_cards_font_faces(): array {
	$theme = wp_get_theme( 'shrishti-heritage' );
	if ( ! $theme->exists() ) {
		return array( '', array() );
	}
	$base  = trailingslashit( $theme->get_stylesheet_directory_uri() ) . 'assets/fonts/';
	$dir   = trailingslashit( $theme->get_stylesheet_directory() ) . 'assets/fonts/';
	$fonts = array(
		'bodoni'  => 'faad168b2dc.woff2', // Bodoni Moda, latin, 400–500.
		'figtree' => 'fdab710cf25.woff2', // Figtree, latin, variable 400–700.
	);
	foreach ( $fonts as $file ) {
		if ( ! file_exists( $dir . $file ) ) {
			return array( '', array() );
		}
	}
	$range = 'U+0000-00FF,U+0131,U+0152-0153,U+02BB-02BC,U+02C6,U+02DA,U+02DC,U+0304,U+0308,U+0329,U+2000-206F,U+20AC,U+2122,U+2191,U+2193,U+2212,U+2215,U+FEFF,U+FFFD';
	$css   = "@font-face{font-family:'Bodoni Moda';font-style:normal;font-weight:400 500;font-display:swap;src:url({$base}{$fonts['bodoni']}) format('woff2');unicode-range:{$range}}"
		. "@font-face{font-family:'Figtree';font-style:normal;font-weight:400 700;font-display:swap;src:url({$base}{$fonts['figtree']}) format('woff2');unicode-range:{$range}}";
	return array( $css, array( $base . $fonts['bodoni'], $base . $fonts['figtree'] ) );
}

/** Small inline SVG icons (stroke style as in the approved sample). */
function shrishti_cards_icon( string $name, int $size = 22, string $width = '1.7' ): string {
	$paths = array(
		'phone'  => '<path d="M5 3h3l2 5-2.5 1.5a11 11 0 0 0 7 7L16 14l5 2v3a2 2 0 0 1-2 2A17 17 0 0 1 3 5a2 2 0 0 1 2-2z"/>',
		'wa'     => '<path d="M3.5 20.5l1.3-4.2A8.5 8.5 0 1 1 8 19.4z"/><path d="M9 8.5c0 3.5 3 6.5 6.5 6.5l1-1.5-2-1-1 .8a5 5 0 0 1-2.8-2.8l.8-1-1-2z"/>',
		'mail'   => '<rect x="3" y="5" width="18" height="14"/><path d="M3 6l9 7 9-7"/>',
		'globe'  => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3.2 3 14.8 0 18M12 3c-3 3.2-3 14.8 0 18"/>',
		'mobile' => '<rect x="7" y="2.5" width="10" height="19" rx="1.5"/><path d="M11 18.5h2"/>',
		'pin'    => '<path d="M12 21s-7-6.1-7-11.5A7 7 0 0 1 19 9.5C19 14.9 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
		'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
		'add'    => '<circle cx="9" cy="8" r="4"/><path d="M2 21c0-3.9 3.1-7 7-7s7 3.1 7 7"/><path d="M19 8v6M16 11h6"/>',
		'in'     => '<rect x="3" y="3" width="18" height="18"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'lang'   => '<path d="M4 5h8M8 3v2M6 5c0 4 3 7 6 8M10 5c0 4-3 7-6 8"/><path d="M13 21l4-9 4 9M14.5 18h5"/>',
		'hello'  => '<path d="M4 5h16v11H9l-5 4z"/><path d="M8 9h8M8 12h5"/>',
	);
	return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" stroke-width="' . $width . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? '' ) . '</svg>';
}
