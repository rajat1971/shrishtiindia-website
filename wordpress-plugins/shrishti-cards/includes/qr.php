<?php
/**
 * QR codes, generated on the server with the bundled chillerlan/php-qrcode library.
 * Error correction M, 2-module quiet zone, ink #0A2A24 on paper. No external QR services.
 */

defined( 'ABSPATH' ) || exit;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Common\EccLevel;

/** QR modules as rows of booleans (true = dark), without quiet zone. */
function shrishti_cards_qr_matrix( string $data ): array {
	static $cache = array();
	if ( isset( $cache[ $data ] ) ) {
		return $cache[ $data ];
	}
	$options = new QROptions(
		array(
			'eccLevel'     => EccLevel::M,
			'addQuietzone' => false,
		)
	);
	$matrix  = ( new QRCode( $options ) )->addByteSegment( $data )->getQRMatrix();
	$n       = $matrix->getSize();
	$rows    = array();
	for ( $y = 0; $y < $n; $y++ ) {
		for ( $x = 0; $x < $n; $x++ ) {
			$rows[ $y ][ $x ] = $matrix->check( $x, $y );
		}
	}
	return $cache[ $data ] = $rows;
}

/** Crisp SVG; horizontal runs of dark modules are merged into one path segment. */
function shrishti_cards_qr_svg( string $data, string $ink = '#0A2A24', string $bg = '#FAF6EE', int $quiet = 2, array $attrs = array() ): string {
	$rows  = shrishti_cards_qr_matrix( $data );
	$n     = count( $rows );
	$total = $n + 2 * $quiet;
	$d     = '';
	foreach ( $rows as $y => $row ) {
		$x = 0;
		while ( $x < $n ) {
			if ( $row[ $x ] ) {
				$start = $x;
				while ( $x < $n && $row[ $x ] ) {
					$x++;
				}
				$d .= 'M' . ( $start + $quiet ) . ' ' . ( $y + $quiet ) . 'h' . ( $x - $start ) . 'v1h-' . ( $x - $start ) . 'z';
			} else {
				$x++;
			}
		}
	}
	$extra = '';
	foreach ( $attrs as $k => $v ) {
		$extra .= ' ' . $k . '="' . esc_attr( $v ) . '"';
	}
	return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' . $total . ' ' . $total . '" shape-rendering="crispEdges"' . $extra . '>'
		. '<rect width="' . $total . '" height="' . $total . '" fill="' . esc_attr( $bg ) . '"/>'
		. '<path fill="' . esc_attr( $ink ) . '" d="' . $d . '"/></svg>';
}

/** PNG of exactly $px × $px (the quiet zone absorbs any rounding). Requires GD. */
function shrishti_cards_qr_png( string $data, int $px = 1024, int $quiet = 2 ): string {
	if ( ! function_exists( 'imagecreatetruecolor' ) ) {
		return '';
	}
	$rows  = shrishti_cards_qr_matrix( $data );
	$n     = count( $rows );
	$scale = max( 1, intdiv( $px, $n + 2 * $quiet ) );
	$off   = intdiv( $px - $n * $scale, 2 );
	$img   = imagecreatetruecolor( $px, $px );
	$bg    = imagecolorallocate( $img, 0xFA, 0xF6, 0xEE );
	$ink   = imagecolorallocate( $img, 0x0A, 0x2A, 0x24 );
	imagefilledrectangle( $img, 0, 0, $px - 1, $px - 1, $bg );
	foreach ( $rows as $y => $row ) {
		foreach ( $row as $x => $dark ) {
			if ( $dark ) {
				$x0 = $off + $x * $scale;
				$y0 = $off + $y * $scale;
				imagefilledrectangle( $img, $x0, $y0, $x0 + $scale - 1, $y0 + $scale - 1, $ink );
			}
		}
	}
	ob_start();
	imagepng( $img, null, 9 );
	return (string) ob_get_clean();
}
