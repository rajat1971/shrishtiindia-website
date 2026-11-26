<?php
/**
 * Unknown (404) or deactivated (410) card: company details only, never another person's details or other codes.
 *
 * @var array $co Company settings.
 */

defined( 'ABSPATH' ) || exit;

list( $font_css, $font_urls ) = shrishti_cards_font_faces();
$office = shrishti_cards_office_phones( $co );
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $co['brand_name'] ); ?></title>
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="theme-color" content="#0F3B33">
<style><?php echo $font_css . file_get_contents( SHRISHTI_CARDS_DIR . 'assets/card.css' ); // phpcs:ignore ?></style>
</head>
<body>
<main class="card">
	<header class="head">
		<div class="head-row">
			<img class="logo" src="<?php echo esc_url( shrishti_cards_logo_url( $co ) ); ?>" alt="Shrishti" height="34">
			<div class="est"><?php echo esc_html( $co['header_line1'] ); ?><br><?php echo esc_html( $co['header_line2'] ); ?></div>
		</div>
		<div class="portrait mono" aria-hidden="true">S</div>
	</header>

	<section class="closed">
		<h1>This card is no longer active</h1>
		<p>You can still reach <?php echo esc_html( $co['brand_name'] ); ?> at our office.</p>
	</section>

	<a class="save" href="<?php echo esc_url( $co['website'] ); ?>"><?php echo shrishti_cards_icon( 'globe', 20, '1.8' ); // phpcs:ignore ?>Visit <?php echo esc_html( preg_replace( '#^https?://(www\.)?#', '', untrailingslashit( $co['website'] ) ) ); ?></a>

	<ul class="details">
		<?php if ( $office ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'phone', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office</span><span class="v">
				<?php
				$links = array();
				foreach ( $office as $phone ) {
					$links[] = '<a href="' . esc_url( 'tel:' . $phone[1] ) . '">' . esc_html( $phone[0] ) . '</a>';
				}
				echo implode( ' / ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
				?>
			</span></div>
			<span></span>
		</li>
		<?php endif; ?>
		<?php if ( '' !== $co['email'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'mail', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Email</span><span class="v"><a href="<?php echo esc_url( 'mailto:' . $co['email'] ); ?>"><?php echo esc_html( $co['email'] ); ?></a></span></div>
			<span></span>
		</li>
		<?php endif; ?>
		<li>
			<?php echo shrishti_cards_icon( 'pin', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office address</span><span class="v"><?php echo esc_html( $co['street'] . ', ' . $co['city'] . ' ' . $co['zip'] ); ?></span></div>
			<?php if ( '' !== $co['map_url'] ) : ?><a class="copy" href="<?php echo esc_url( $co['map_url'] ); ?>" target="_blank" rel="noopener">Map<span class="sr"> of the office</span></a><?php else : ?><span></span><?php endif; ?>
		</li>
		<?php if ( '' !== $co['hours'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'clock', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office hours</span><span class="v"><?php echo esc_html( $co['hours'] ); ?></span></div>
			<span></span>
		</li>
		<?php endif; ?>
	</ul>

	<?php if ( '' !== $co['credentials'] ) : ?>
	<div class="creds"><?php echo wp_kses( $co['credentials'], array( 'b' => array(), 'strong' => array() ) ); ?></div>
	<?php endif; ?>
	<footer class="foot"><span><?php echo esc_html( $co['company_name'] ); ?></span><span></span></footer>
</main>
</body>
</html>
