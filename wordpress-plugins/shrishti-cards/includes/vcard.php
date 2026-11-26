<?php
/**
 * vCard 3.0 (spec §6): UTF-8, CRLF, lines folded at 75 octets, \ , ; escaped, photo embedded.
 */

defined( 'ABSPATH' ) || exit;

function shrishti_cards_vcard_escape( string $s ): string {
	return str_replace( array( '\\', ',', ';', "\r\n", "\n", "\r" ), array( '\\\\', '\\,', '\\;', '\\n', '\\n', '\\n' ), $s );
}

/** Fold one content line at 75 octets without splitting a UTF-8 character. */
function shrishti_cards_vcard_fold( string $line ): string {
	if ( strlen( $line ) <= 75 ) {
		return $line;
	}
	if ( ! preg_match( '/[\x80-\xff]/', $line ) ) {
		return substr( $line, 0, 75 ) . "\r\n " . implode( "\r\n ", str_split( substr( $line, 75 ), 74 ) );
	}
	$out   = '';
	$cur   = '';
	$limit = 75;
	foreach ( preg_split( '//u', $line, -1, PREG_SPLIT_NO_EMPTY ) as $ch ) {
		if ( strlen( $cur ) + strlen( $ch ) > $limit ) {
			$out  .= $cur . "\r\n ";
			$cur   = '';
			$limit = 74; // The leading space of a continuation line counts.
		}
		$cur .= $ch;
	}
	return $out . $cur;
}

/** Base64 JPEG of the photo at 240×240, kept under ~25 KB. Cached on the attachment. */
function shrishti_cards_vcard_photo( int $attachment_id ): string {
	$file = $attachment_id ? get_attached_file( $attachment_id ) : '';
	if ( ! $file || ! file_exists( $file ) ) {
		return '';
	}
	$key    = md5( $file . '|' . filemtime( $file ) );
	$cached = get_post_meta( $attachment_id, '_shrishti_card_vphoto', true );
	if ( is_array( $cached ) && ( $cached['key'] ?? '' ) === $key ) {
		return (string) $cached['b64'];
	}
	$bytes = '';
	foreach ( array( 82, 70, 58 ) as $quality ) {
		$editor = wp_get_image_editor( $file );
		if ( is_wp_error( $editor ) ) {
			return '';
		}
		$editor->resize( 240, 240, true );
		$editor->set_quality( $quality );
		$tmp   = trailingslashit( get_temp_dir() ) . 'sc-' . wp_generate_password( 10, false ) . '.jpg';
		$saved = $editor->save( $tmp, 'image/jpeg' );
		if ( is_wp_error( $saved ) ) {
			return '';
		}
		$bytes = (string) file_get_contents( $saved['path'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		wp_delete_file( $saved['path'] );
		if ( strlen( $bytes ) <= 25000 ) {
			break;
		}
	}
	$b64 = base64_encode( $bytes ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions
	update_post_meta(
		$attachment_id,
		'_shrishti_card_vphoto',
		array(
			'key' => $key,
			'b64' => $b64,
		)
	);
	return $b64;
}

function shrishti_cards_vcard( array $c, array $co ): string {
	$e     = 'shrishti_cards_vcard_escape';
	$lines = array(
		'BEGIN:VCARD',
		'VERSION:3.0',
		'N:' . $e( $c['last'] ) . ';' . $e( $c['first'] ) . ';;;',
		'FN:' . $e( $c['name'] ),
		'ORG:' . $e( $co['company_name'] ) . ( '' !== $c['department'] ? ';' . $e( $c['department'] ) : '' ),
	);
	if ( '' !== $c['designation'] ) {
		$lines[] = 'TITLE:' . $e( $c['designation'] );
	}
	if ( '' !== $c['mobile_e164'] ) {
		$lines[] = 'TEL;TYPE=CELL,VOICE:' . $c['mobile_e164'];
	}
	if ( $c['wa_separate'] ) {
		$lines[] = 'TEL;TYPE=CELL:' . $c['wa_e164'];
	}
	foreach ( array_slice( shrishti_cards_office_phones( $co ), 0, 2 ) as $phone ) {
		$lines[] = 'TEL;TYPE=WORK,VOICE:' . $phone[1];
	}
	if ( '' !== $c['email'] ) {
		$lines[] = 'EMAIL;TYPE=INTERNET,WORK,PREF:' . $c['email'];
	}
	foreach ( $c['role_list'] as $r ) {
		if ( '' !== $r['email'] ) {
			$lines[] = 'EMAIL;TYPE=INTERNET,WORK:' . $r['email'];
		}
	}
	$lines[] = 'ADR;TYPE=WORK:;;' . $e( $co['street'] ) . ';' . $e( $co['city'] ) . ';' . $e( $co['region'] ) . ';' . $e( $co['zip'] ) . ';' . $e( $co['country'] );
	if ( '' !== $co['website'] ) {
		$lines[] = 'URL;TYPE=WORK:' . $co['website'];
	}
	$lines[] = 'URL:' . $c['url'];
	foreach ( $c['role_list'] as $r ) {
		if ( '' !== $r['url'] ) {
			$lines[] = 'URL:' . $r['url'];
		}
	}
	if ( '' !== $c['linkedin'] ) {
		$lines[] = 'X-SOCIALPROFILE;TYPE=linkedin:' . $c['linkedin'];
	}
	$note = array();
	foreach ( $c['role_list'] as $r ) {
		$note[] = $r['role'] . ', ' . $r['org'] . ( '' !== $r['url'] ? ' (' . preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $r['url'] ) ) . ')' : '' ) . '.';
	}
	if ( '' !== $co['vcard_note'] ) {
		$note[] = $co['vcard_note'];
	}
	if ( $note ) {
		$lines[] = 'NOTE:' . $e( implode( ' ', $note ) );
	}
	$photo = shrishti_cards_vcard_photo( (int) $c['photo_att'] );
	if ( '' !== $photo ) {
		$lines[] = 'PHOTO;ENCODING=b;TYPE=JPEG:' . $photo;
	}
	$lines[] = 'REV:' . gmdate( 'Y-m-d\TH:i:s\Z', strtotime( $c['modified'] . ' UTC' ) );
	$lines[] = 'END:VCARD';
	return implode( "\r\n", array_map( 'shrishti_cards_vcard_fold', $lines ) ) . "\r\n";
}
