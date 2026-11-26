<?php
/**
 * Admin: Smart Cards post type, card editor, list columns, QR downloads, email signature (spec §5, §7, §8, §9).
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', 'shrishti_cards_register_post_type' );

function shrishti_cards_register_post_type(): void {
	register_post_type(
		'shrishti_card',
		array(
			'labels'              => array(
				'name'               => 'Smart Cards',
				'singular_name'      => 'Smart Card',
				'menu_name'          => 'Smart Cards',
				'all_items'          => 'All cards',
				'add_new'            => 'Add card',
				'add_new_item'       => 'Add new card',
				'edit_item'          => 'Edit card',
				'search_items'       => 'Search cards',
				'not_found'          => 'No cards yet.',
				'not_found_in_trash' => 'No cards in Trash.',
			),
			'public'              => false,
			'publicly_queryable'  => false,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => false,
			'show_in_admin_bar'   => false,
			'show_in_rest'        => false,
			'has_archive'         => false,
			'rewrite'             => false,
			'query_var'           => false,
			'supports'            => array( 'revisions' ),
			'menu_icon'           => 'dashicons-id-alt',
			'menu_position'       => 21,
			'capability_type'     => 'page',
			'map_meta_cap'        => true,
		)
	);
}

/* ---------- Editor ---------- */

add_action(
	'add_meta_boxes_shrishti_card',
	function () {
		add_meta_box( 'sc_details', 'Card details', 'shrishti_cards_box_details', 'shrishti_card', 'normal', 'high' );
		add_meta_box( 'sc_links', 'Card links & QR', 'shrishti_cards_box_links', 'shrishti_card', 'side', 'high' );
		add_meta_box( 'sc_signature', 'Email signature', 'shrishti_cards_box_signature', 'shrishti_card', 'normal', 'default' );
	}
);

add_action(
	'admin_enqueue_scripts',
	function ( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ( 'shrishti_card' !== $screen->post_type && 'settings_page_shrishti-cards' !== $screen->id ) ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'shrishti-cards-admin', SHRISHTI_CARDS_URL . 'assets/admin.js', array( 'jquery' ), SHRISHTI_CARDS_VERSION, true );
		wp_enqueue_style( 'shrishti-cards-admin', SHRISHTI_CARDS_URL . 'assets/admin.css', array(), SHRISHTI_CARDS_VERSION );
	}
);

/** A media-library picker field storing an attachment ID. */
function shrishti_cards_image_field( string $name, int $id, string $button = 'Choose photo' ): void {
	$src = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
	echo '<div class="sc-image">';
	echo '<img class="sc-image-preview" src="' . esc_url( (string) $src ) . '" alt=""' . ( $src ? '' : ' hidden' ) . '>';
	echo '<input type="hidden" name="' . esc_attr( $name ) . '" value="' . esc_attr( (string) $id ) . '">';
	echo '<button type="button" class="button sc-image-pick">' . esc_html( $button ) . '</button> ';
	echo '<button type="button" class="button-link sc-image-clear"' . ( $id ? '' : ' hidden' ) . '>Remove</button>';
	echo '</div>';
}

function shrishti_cards_box_details( WP_Post $post ): void {
	$c = array();
	foreach ( shrishti_cards_fields() as $key => $default ) {
		$v         = get_post_meta( $post->ID, '_sc_' . $key, true );
		$c[ $key ] = ( '' === $v || false === $v ) ? $default : (string) $v;
	}
	wp_nonce_field( 'shrishti_card_save', 'shrishti_card_nonce' );
	$text = function ( $key, $label, $help = '', $type = 'text', $attrs = '' ) use ( $c ) {
		echo '<tr><th scope="row"><label for="sc-' . esc_attr( $key ) . '">' . esc_html( $label ) . '</label></th><td>';
		echo '<input class="regular-text" type="' . esc_attr( $type ) . '" id="sc-' . esc_attr( $key ) . '" name="sc[' . esc_attr( $key ) . ']" value="' . esc_attr( $c[ $key ] ) . '" ' . $attrs . '>'; // phpcs:ignore WordPress.Security.EscapeOutput -- $attrs are literals below.
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	};
	echo '<table class="form-table sc-form" role="presentation"><tbody>';
	$text( 'code', 'Card code', 'Two letters and three digits, e.g. gg007. The card opens at ' . shrishti_cards_short_url( 'gg007' ) . '.', 'text', 'pattern="[A-Za-z]{2}[0-9]{3}" maxlength="5" required style="width:8em;text-transform:lowercase"' );
	echo '<tr><th scope="row">Active</th><td><label><input type="checkbox" name="sc[active]" value="1"' . checked( '1', $c['active'], false ) . '> Card is live</label><p class="description">Untick when someone leaves: the address then shows the company card only and Save to contacts stops working.</p></td></tr>';
	$text( 'first', 'First name', '', 'text', 'required' );
	$text( 'last', 'Last name' );
	$text( 'designation', 'Designation', 'e.g. Director' );
	$text( 'department', 'Department', 'Optional, e.g. Corporate desk, Visas' );
	echo '<tr><th scope="row">Photo</th><td>';
	shrishti_cards_image_field( 'sc[photo_id]', (int) $c['photo_id'] );
	echo '<p class="description">Square, at least 400×400 px, face centred. Without a photo the card shows initials.</p></td></tr>';
	$text( 'mobile', 'Mobile', 'Ten-digit Indian numbers get +91 automatically.', 'tel' );
	$text( 'whatsapp', 'WhatsApp number', 'Only if different from the mobile.', 'tel' );
	$text( 'email', 'Email', '', 'email' );
	$text( 'linkedin', 'LinkedIn URL', 'Optional', 'url' );
	$text( 'wa_text', 'WhatsApp greeting', 'Optional text pre-filled when a visitor taps WhatsApp. Default: "Hello ' . ( $c['first'] ? $c['first'] : 'Name' ) . ', "' );
	$text( 'languages', 'Languages spoken', 'Optional, e.g. English, Hindi, Marathi' );
	echo '<tr><th scope="row">Say hello form</th><td><label><input type="checkbox" name="sc[hello]" value="1"' . checked( '1', $c['hello'], false ) . '> Show "Say hello" on the card</label><p class="description">Visitors can enter their name, company, mobile and email; WhatsApp then opens on their phone with the message addressed to this person\'s WhatsApp. Nothing is stored on the website.</p></td></tr>';
	echo '<tr><th scope="row"><label for="sc-note">Internal note</label></th><td><textarea id="sc-note" name="sc[note]" rows="2" class="large-text">' . esc_textarea( $c['note'] ) . '</textarea><p class="description">Admin only, never shown on the card (e.g. consent date).</p></td></tr>';
	echo '</tbody></table>';
}

function shrishti_cards_box_links( WP_Post $post ): void {
	$code = (string) get_post_meta( $post->ID, '_sc_code', true );
	if ( '' === $code ) {
		echo '<p>Save the card with a code to get its links and QR code.</p>';
		return;
	}
	$live  = shrishti_cards_is_live( $post );
	$url   = home_url( '/' . $code );
	$nonce = wp_create_nonce( 'shrishti_cards_qr_' . $post->ID );
	$qr    = function ( $fmt ) use ( $post, $nonce ) {
		return admin_url( 'admin-post.php?action=shrishti_cards_qr&post=' . $post->ID . '&fmt=' . $fmt . '&_wpnonce=' . $nonce );
	};
	echo '<p><strong>' . esc_html( shrishti_cards_short_url( $code ) ) . '</strong><br>' . ( $live ? '<span class="sc-live">Live</span>' : '<span class="sc-off">Not live</span> (draft or inactive)' ) . '</p>';
	echo '<div class="sc-qr">' . shrishti_cards_qr_svg( $url, '#0A2A24', '#FFFFFF', 2 ) . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- generated SVG.
	echo '<p class="sc-buttons">';
	echo '<a class="button" href="' . esc_url( $live ? $url : add_query_arg( 'preview', '1', $url ) ) . '" target="_blank" rel="noopener">' . ( $live ? 'View card' : 'Preview card' ) . '</a> ';
	if ( $live ) {
		echo '<a class="button" href="' . esc_url( home_url( '/' . $code . '.vcf' ) ) . '">vCard</a> ';
	}
	echo '</p><p class="sc-buttons">';
	echo '<a class="button" href="' . esc_url( $qr( 'png' ) ) . '">Download QR (PNG)</a> ';
	echo '<a class="button" href="' . esc_url( $qr( 'svg' ) ) . '">QR (SVG)</a>';
	echo '</p>';
	echo '<p class="description">PNG is 1024 px for print; SVG scales to any size.</p>';
	echo '<p>Saved to contacts: <strong>' . (int) get_post_meta( $post->ID, '_sc_saves', true ) . '</strong> times</p>';
}

/** Outlook-ready signature HTML (inline styles, table layout). */
function shrishti_cards_signature_html( array $c, array $co, bool $with_qr ): string {
	$office = shrishti_cards_office_phones( $co );
	$html   = '<table cellpadding="0" cellspacing="0" border="0" style="font-family:Arial,Helvetica,sans-serif;font-size:13px;line-height:1.45;color:#1B2320"><tr>';
	if ( $with_qr ) {
		$html .= '<td style="padding:0 14px 0 0;vertical-align:top"><a href="' . esc_url( $c['url'] ) . '"><img src="' . esc_url( $c['png_url'] ) . '" width="76" height="76" alt="QR code: ' . esc_attr( $c['short'] ) . '" style="display:block;border:0"></a></td>';
	}
	$html .= '<td style="vertical-align:top;border-left:2px solid #C7A263;padding:0 0 0 12px">';
	$html .= '<strong style="font-size:15px;color:#0F3B33">' . esc_html( $c['name'] ) . '</strong><br>';
	if ( '' !== $c['designation'] ) {
		$html .= esc_html( $c['designation'] ) . '<br>';
	}
	$html .= '<span style="color:#7A5C24;font-weight:bold">' . esc_html( $co['brand_name'] ) . '</span><br>';
	if ( $office ) {
		$html .= 'Office <a href="' . esc_url( 'tel:' . $office[0][1] ) . '" style="color:#1B2320;text-decoration:none">' . esc_html( $office[0][0] ) . '</a><br>';
	}
	$html .= '<a href="' . esc_url( $c['url'] ) . '" style="color:#0F3B33">' . esc_html( $c['short'] ) . '</a>';
	$html .= '</td></tr></table>';
	return $html;
}

function shrishti_cards_box_signature( WP_Post $post ): void {
	if ( '' === (string) get_post_meta( $post->ID, '_sc_code', true ) ) {
		echo '<p>Save the card with a code first.</p>';
		return;
	}
	$c  = shrishti_cards_get( $post );
	$co = shrishti_cards_company();
	foreach ( array( true, false ) as $with_qr ) {
		$html = shrishti_cards_signature_html( $c, $co, $with_qr );
		echo '<div class="sc-sig" data-variant="' . ( $with_qr ? 'qr' : 'plain' ) . '"' . ( $with_qr ? ' hidden' : '' ) . '>';
		echo '<div class="sc-sig-preview">' . $html . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput -- built with escaping above.
		echo '<textarea class="large-text code sc-sig-html" rows="3" readonly>' . esc_textarea( $html ) . '</textarea>';
		echo '</div>';
	}
	echo '<p><label><input type="checkbox" class="sc-sig-qr"> Include small QR code</label></p>';
	echo '<p><button type="button" class="button button-primary sc-sig-copy">Copy signature</button> <span class="sc-sig-done" hidden>Copied. In Outlook: File → Options → Mail → Signatures, then paste.</span></p>';
}

/* ---------- Save ---------- */

add_action(
	'save_post_shrishti_card',
	function ( $post_id, $post ) {
		if ( ! isset( $_POST['shrishti_card_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['shrishti_card_nonce'] ), 'shrishti_card_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in  = isset( $_POST['sc'] ) && is_array( $_POST['sc'] ) ? wp_unslash( $_POST['sc'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- sanitised per field below.
		$old = (string) get_post_meta( $post_id, '_sc_code', true );

		$code = strtolower( trim( (string) ( $in['code'] ?? '' ) ) );
		if ( ! preg_match( '/^' . SHRISHTI_CARDS_CODE_RE . '$/', $code ) ) {
			shrishti_cards_notice( 'The card code must be two letters and three digits, e.g. gg007. The code was not saved.' );
		} else {
			$other = shrishti_cards_find( $code );
			if ( $other && $other->ID !== $post_id ) {
				shrishti_cards_notice( sprintf( 'The code %s is already used by another card. The code was not saved.', $code ) );
			} else {
				update_post_meta( $post_id, '_sc_code', $code );
				$page = get_page_by_path( $code );
				if ( $page ) {
					shrishti_cards_notice( sprintf( 'Note: a page with the address /%s/ exists. The card takes priority at that address.', $code ) );
				}
			}
		}

		$text = array( 'first', 'last', 'designation', 'department', 'mobile', 'whatsapp', 'wa_text', 'languages' );
		foreach ( $text as $key ) {
			update_post_meta( $post_id, '_sc_' . $key, sanitize_text_field( (string) ( $in[ $key ] ?? '' ) ) );
		}
		update_post_meta( $post_id, '_sc_email', sanitize_email( (string) ( $in['email'] ?? '' ) ) );
		update_post_meta( $post_id, '_sc_linkedin', esc_url_raw( (string) ( $in['linkedin'] ?? '' ), array( 'https', 'http' ) ) );
		update_post_meta( $post_id, '_sc_photo_id', (string) absint( $in['photo_id'] ?? 0 ) );
		update_post_meta( $post_id, '_sc_note', sanitize_textarea_field( (string) ( $in['note'] ?? '' ) ) );
		update_post_meta( $post_id, '_sc_active', empty( $in['active'] ) ? '0' : '1' );
		update_post_meta( $post_id, '_sc_hello', empty( $in['hello'] ) ? '0' : '1' );

		shrishti_cards_purge( $old );
		shrishti_cards_purge( (string) get_post_meta( $post_id, '_sc_code', true ) );
	},
	10,
	2
);

/* The list title is the person's name. */
add_filter(
	'wp_insert_post_data',
	function ( $data, $postarr ) {
		if ( 'shrishti_card' === $data['post_type'] && isset( $_POST['sc'], $_POST['shrishti_card_nonce'] ) && wp_verify_nonce( sanitize_key( $_POST['shrishti_card_nonce'] ), 'shrishti_card_save' ) ) {
			$in   = wp_unslash( $_POST['sc'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			$name = trim( sanitize_text_field( (string) ( $in['first'] ?? '' ) ) . ' ' . sanitize_text_field( (string) ( $in['last'] ?? '' ) ) );
			if ( '' !== $name ) {
				$data['post_title'] = $name;
			}
		}
		return $data;
	},
	10,
	2
);

function shrishti_cards_notice( string $msg ): void {
	$key  = 'shrishti_cards_notice_' . get_current_user_id();
	$list = (array) get_transient( $key );
	$list[] = $msg;
	set_transient( $key, array_filter( $list ), 60 );
}

add_action(
	'admin_notices',
	function () {
		$key  = 'shrishti_cards_notice_' . get_current_user_id();
		$list = get_transient( $key );
		if ( $list ) {
			delete_transient( $key );
			foreach ( (array) $list as $msg ) {
				echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html( $msg ) . '</p></div>';
			}
		}
	}
);

/* ---------- List screen ---------- */

add_filter(
	'manage_shrishti_card_posts_columns',
	function () {
		return array(
			'cb'             => '<input type="checkbox">',
			'title'          => 'Name',
			'sc_code'        => 'Code',
			'sc_designation' => 'Designation',
			'sc_active'      => 'Live',
			'sc_saves'       => 'Saves',
			'sc_links'       => 'Links',
		);
	}
);

add_action(
	'manage_shrishti_card_posts_custom_column',
	function ( $col, $post_id ) {
		$code = (string) get_post_meta( $post_id, '_sc_code', true );
		switch ( $col ) {
			case 'sc_code':
				echo '<code>' . esc_html( $code ) . '</code>';
				break;
			case 'sc_designation':
				echo esc_html( (string) get_post_meta( $post_id, '_sc_designation', true ) );
				break;
			case 'sc_active':
				echo shrishti_cards_is_live( get_post( $post_id ) ) ? '<span class="sc-live">Yes</span>' : '<span class="sc-off">No</span>';
				break;
			case 'sc_saves':
				echo (int) get_post_meta( $post_id, '_sc_saves', true );
				break;
			case 'sc_links':
				if ( '' === $code ) {
					break;
				}
				$nonce = wp_create_nonce( 'shrishti_cards_qr_' . $post_id );
				$qr    = admin_url( 'admin-post.php?action=shrishti_cards_qr&post=' . $post_id . '&_wpnonce=' . $nonce . '&fmt=' );
				echo '<a href="' . esc_url( home_url( '/' . $code ) ) . '" target="_blank" rel="noopener">View</a> · ';
				echo '<a href="' . esc_url( home_url( '/' . $code . '.vcf' ) ) . '">vCard</a> · ';
				echo '<a href="' . esc_url( $qr . 'png' ) . '">QR PNG</a> · ';
				echo '<a href="' . esc_url( $qr . 'svg' ) . '">SVG</a>';
				break;
		}
	},
	10,
	2
);

/* ---------- QR downloads (admin only) ---------- */

add_action(
	'admin_post_shrishti_cards_qr',
	function () {
		$post_id = absint( $_GET['post'] ?? 0 );
		check_admin_referer( 'shrishti_cards_qr_' . $post_id );
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die( 'Not allowed.', 403 );
		}
		$code = (string) get_post_meta( $post_id, '_sc_code', true );
		if ( '' === $code ) {
			wp_die( 'This card has no code yet.' );
		}
		$url = home_url( '/' . $code );
		$fmt = 'svg' === ( $_GET['fmt'] ?? '' ) ? 'svg' : 'png';
		nocache_headers();
		if ( 'svg' === $fmt ) {
			header( 'Content-Type: image/svg+xml; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $code . '-qr.svg"' );
			echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . shrishti_cards_qr_svg( $url, '#0A2A24', '#FFFFFF', 2 ); // phpcs:ignore WordPress.Security.EscapeOutput
		} else {
			$png = shrishti_cards_qr_png( $url, 1024 );
			if ( '' === $png ) {
				wp_die( 'PNG needs the GD image extension on the server. Use the SVG instead.' );
			}
			header( 'Content-Type: image/png' );
			header( 'Content-Disposition: attachment; filename="' . $code . '-qr.png"' );
			echo $png; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		exit;
	}
);
