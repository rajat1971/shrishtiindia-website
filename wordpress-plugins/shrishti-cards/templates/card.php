<?php
/**
 * Card page: a standalone minimal document (spec §4, §8).
 *
 * @var array $c       Card data from shrishti_cards_get().
 * @var array $co      Company settings.
 * @var bool  $preview True when an editor previews an inactive card.
 */

defined( 'ABSPATH' ) || exit;

list( $font_css, $font_urls ) = shrishti_cards_font_faces();
$office  = shrishti_cards_office_phones( $co );
$groups  = shrishti_cards_group_links( $co );
$title   = $c['name'] . ( '' !== $c['designation'] ? ' · ' . $c['designation'] : '' );
$role    = trim( $c['designation'] . ( '' !== $c['department'] ? ' · ' . $c['department'] : '' ), ' ·' );
$wa_link = 'https://wa.me/' . $c['wa_digits'] . '?text=' . rawurlencode( $c['wa_greeting'] );
$n_quick = 1 + ( '' !== $c['mobile_e164'] ? 1 : 0 ) + ( '' !== $c['wa_digits'] ? 1 : 0 ) + ( '' !== $c['email'] ? 1 : 0 );
?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?php echo esc_html( $c['name'] . ' · ' . $co['brand_name'] ); ?></title>
<meta name="robots" content="noindex, nofollow, noarchive">
<meta name="description" content="<?php echo esc_attr( $title . ', ' . $co['brand_name'] ); ?>">
<meta name="theme-color" content="#0F3B33">
<meta property="og:type" content="profile">
<meta property="og:title" content="<?php echo esc_attr( $title ); ?>">
<meta property="og:description" content="<?php echo esc_attr( $co['brand_name'] . ' · ' . $c['short'] ); ?>">
<meta property="og:url" content="<?php echo esc_url( $c['url'] ); ?>">
<meta property="og:site_name" content="<?php echo esc_attr( $co['brand_name'] ); ?>">
<meta property="profile:first_name" content="<?php echo esc_attr( $c['first'] ); ?>">
<meta property="profile:last_name" content="<?php echo esc_attr( $c['last'] ); ?>">
<?php if ( $c['og_image'] ) : ?>
<meta property="og:image" content="<?php echo esc_url( $c['og_image'] ); ?>">
<meta property="og:image:alt" content="<?php echo esc_attr( $c['name'] ); ?>">
<meta name="twitter:card" content="summary">
<?php endif; ?>
<?php foreach ( $font_urls as $font_url ) : ?>
<link rel="preload" href="<?php echo esc_url( $font_url ); ?>" as="font" type="font/woff2" crossorigin>
<?php endforeach; ?>
<style><?php echo $font_css . file_get_contents( SHRISHTI_CARDS_DIR . 'assets/card.css' ); // phpcs:ignore ?></style>
</head>
<body>
<?php if ( ! empty( $preview ) ) : ?>
<p class="preview-note">Preview: this card is not live. Visitors see the "no longer active" page.</p>
<?php endif; ?>

<main class="card">
	<header class="head">
		<div class="head-row">
			<img class="logo" src="<?php echo esc_url( shrishti_cards_logo_url( $co ) ); ?>" alt="Shrishti" height="34">
			<div class="est"><?php echo esc_html( $co['header_line1'] ); ?><br><?php echo esc_html( $co['header_line2'] ); ?></div>
		</div>
		<?php if ( $c['photo_url'] ) : ?>
			<img class="portrait" src="<?php echo esc_url( $c['photo_url'] ); ?>" alt="<?php echo esc_attr( $c['name'] ); ?>" width="124" height="124">
		<?php else : ?>
			<div class="portrait mono" aria-hidden="true"><?php echo esc_html( $c['initials'] ); ?></div>
		<?php endif; ?>
	</header>

	<section class="who">
		<h1><?php echo esc_html( $c['name'] ); ?></h1>
		<?php if ( '' !== $role ) : ?><p class="role"><?php echo esc_html( $role ); ?></p><?php endif; ?>
		<p class="org"><?php echo esc_html( $co['brand_name'] ); ?></p>
	</section>

	<a class="save" href="<?php echo esc_url( $c['vcf_url'] ); ?>">
		<?php echo shrishti_cards_icon( 'add', 20, '1.8' ); // phpcs:ignore ?>
		Save to contacts
	</a>

	<nav class="quick<?php echo 3 === $n_quick ? ' n3' : ''; ?>" aria-label="Quick actions">
		<?php if ( '' !== $c['mobile_e164'] ) : ?>
			<a href="<?php echo esc_url( 'tel:' . $c['mobile_e164'] ); ?>"><?php echo shrishti_cards_icon( 'phone' ); // phpcs:ignore ?>Call</a>
		<?php endif; ?>
		<?php if ( '' !== $c['wa_digits'] ) : ?>
			<a class="wa" href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener"><?php echo shrishti_cards_icon( 'wa' ); // phpcs:ignore ?>WhatsApp</a>
		<?php endif; ?>
		<?php if ( '' !== $c['email'] ) : ?>
			<a href="<?php echo esc_url( 'mailto:' . $c['email'] ); ?>"><?php echo shrishti_cards_icon( 'mail' ); // phpcs:ignore ?>Email</a>
		<?php endif; ?>
		<a href="<?php echo esc_url( $co['website'] ); ?>" target="_blank" rel="noopener"><?php echo shrishti_cards_icon( 'globe' ); // phpcs:ignore ?>Website</a>
	</nav>

	<?php if ( '1' === $c['hello'] && '' !== $c['wa_digits'] ) : ?>
	<details class="hello">
		<summary>
			<?php echo shrishti_cards_icon( 'hello', 22 ); // phpcs:ignore ?>
			<span class="ht">
				<span class="hn"><?php echo esc_html( sprintf( 'Say hello to %s', $c['first'] ) ); ?></span>
				<span class="hd"><?php echo esc_html( sprintf( 'Share your details with %s on WhatsApp', $c['first'] ) ); ?></span>
			</span>
			<span class="tog" aria-hidden="true">+</span>
		</summary>
		<form id="hello" data-wa="<?php echo esc_attr( $c['wa_digits'] ); ?>" data-greeting="<?php echo esc_attr( sprintf( 'Hello %s,', $c['first'] ) ); ?>" autocomplete="on">
			<div class="grid">
				<div><label for="h-first">First name</label><input id="h-first" name="first" required autocomplete="given-name" maxlength="60"></div>
				<div><label for="h-last">Last name</label><input id="h-last" name="last" required autocomplete="family-name" maxlength="60"></div>
				<div class="full"><label for="h-company">Company <i>(optional)</i></label><input id="h-company" name="company" autocomplete="organization" maxlength="100"></div>
				<div class="full"><label for="h-mobile">Mobile</label><input id="h-mobile" name="mobile" type="tel" required autocomplete="tel" inputmode="tel" maxlength="20" placeholder="+91"></div>
				<div class="full"><label for="h-email">Email <i>(optional)</i></label><input id="h-email" name="email" type="email" autocomplete="email" maxlength="120"></div>
			</div>
			<button class="save send" type="submit"><?php echo shrishti_cards_icon( 'wa', 20, '1.8' ); // phpcs:ignore ?><?php echo esc_html( sprintf( 'Send to %s on WhatsApp', $c['first'] ) ); ?></button>
			<p class="fine">WhatsApp opens with your message ready; you press Send. Nothing is stored on this website.</p>
			<p class="err" id="hello-err" role="alert" hidden></p>
		</form>
	</details>
	<?php endif; ?>

	<ul class="details">
		<?php if ( '' !== $c['mobile_e164'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'mobile', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k"><?php echo $c['wa_separate'] ? 'Mobile' : 'Mobile / WhatsApp'; ?></span><span class="v"><a href="<?php echo esc_url( 'tel:' . $c['mobile_e164'] ); ?>"><?php echo esc_html( shrishti_cards_pretty_phone( $c['mobile_e164'] ) ); ?></a></span></div>
			<button class="copy" type="button" data-copy="<?php echo esc_attr( $c['mobile_e164'] ); ?>">Copy<span class="sr"> mobile number</span></button>
		</li>
		<?php endif; ?>
		<?php if ( $c['wa_separate'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'wa', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">WhatsApp</span><span class="v"><a href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener"><?php echo esc_html( shrishti_cards_pretty_phone( $c['wa_e164'] ) ); ?></a></span></div>
			<button class="copy" type="button" data-copy="<?php echo esc_attr( $c['wa_e164'] ); ?>">Copy<span class="sr"> WhatsApp number</span></button>
		</li>
		<?php endif; ?>
		<?php if ( $office ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'phone', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office</span><span class="v">
				<?php
				$links = array();
				foreach ( $office as $i => $phone ) {
					// Later numbers show only their last part, as on the sample: "+91-22-22053535 / 22053636".
					$label   = $i > 0 && false !== strrpos( $phone[0], '-' ) ? substr( $phone[0], strrpos( $phone[0], '-' ) + 1 ) : $phone[0];
					$links[] = '<a href="' . esc_url( 'tel:' . $phone[1] ) . '">' . esc_html( $label ) . '</a>';
				}
				echo implode( ' / ', $links ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped above.
				?>
			</span></div>
			<button class="copy" type="button" data-copy="<?php echo esc_attr( $office[0][0] ); ?>">Copy<span class="sr"> office number</span></button>
		</li>
		<?php endif; ?>
		<?php if ( '' !== $c['email'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'mail', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Email</span><span class="v"><a href="<?php echo esc_url( 'mailto:' . $c['email'] ); ?>"><?php echo esc_html( $c['email'] ); ?></a></span></div>
			<button class="copy" type="button" data-copy="<?php echo esc_attr( $c['email'] ); ?>">Copy<span class="sr"> email address</span></button>
		</li>
		<?php endif; ?>
		<?php if ( '' !== $c['linkedin'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'in', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">LinkedIn</span><span class="v"><a href="<?php echo esc_url( $c['linkedin'] ); ?>" target="_blank" rel="noopener">View profile</a></span></div>
			<span></span>
		</li>
		<?php endif; ?>
		<li>
			<?php echo shrishti_cards_icon( 'pin', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office address</span><span class="v"><?php echo esc_html( $co['street'] . ', ' . $co['city'] . ' ' . $co['zip'] ); ?></span></div>
			<?php if ( '' !== $co['map_url'] ) : ?>
				<a class="copy" href="<?php echo esc_url( $co['map_url'] ); ?>" target="_blank" rel="noopener">Map<span class="sr"> of the office</span></a>
			<?php else : ?>
				<span></span>
			<?php endif; ?>
		</li>
		<?php if ( '' !== $co['hours'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'clock', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Office hours</span><span class="v"><?php echo implode( ' · ', array_map( function ( $part ) { return '<span class="nw">' . esc_html( trim( $part ) ) . '</span>'; }, explode( '·', $co['hours'] ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput -- escaped per part. ?></span></div>
			<span></span>
		</li>
		<?php endif; ?>
		<?php if ( '' !== $c['languages'] ) : ?>
		<li>
			<?php echo shrishti_cards_icon( 'lang', 20, '1.6' ); // phpcs:ignore ?>
			<div><span class="k">Languages</span><span class="v"><?php echo esc_html( $c['languages'] ); ?></span></div>
			<span></span>
		</li>
		<?php endif; ?>
	</ul>

	<?php if ( $groups ) : ?>
	<section class="block">
		<p class="label">The Shrishti group</p>
		<div class="div-list">
			<?php foreach ( $groups as $g ) : ?>
			<a href="<?php echo esc_url( $g['url'] ); ?>" target="_blank" rel="noopener"><span><span class="dn"><?php echo esc_html( $g['name'] ); ?></span><span class="dd"><?php echo esc_html( $g['desc'] ); ?></span></span><span class="arrow" aria-hidden="true">↗</span></a>
			<?php endforeach; ?>
		</div>
	</section>
	<?php endif; ?>

	<section class="share" aria-label="Share this card">
		<div class="qr"><?php echo shrishti_cards_qr_svg( $c['url'], '#0A2A24', '#FAF6EE', 2, array( 'role' => 'img', 'aria-label' => 'QR code for ' . $c['short'] ) ); // phpcs:ignore ?></div>
		<div>
			<p>Scan or share this card</p>
			<div class="url"><?php echo esc_html( $c['short'] ); ?></div>
			<div class="btns">
				<button class="copy" type="button" data-copy="<?php echo esc_attr( $c['url'] ); ?>">Copy link</button>
				<button class="copy" type="button" id="share" data-url="<?php echo esc_attr( $c['url'] ); ?>" data-title="<?php echo esc_attr( $title ); ?>" hidden>Share</button>
			</div>
		</div>
	</section>

	<?php if ( '' !== $co['credentials'] ) : ?>
	<div class="creds"><?php echo wp_kses( $co['credentials'], array( 'b' => array(), 'strong' => array() ) ); ?></div>
	<?php endif; ?>
	<footer class="foot"><span><?php echo esc_html( $co['company_name'] ); ?></span><span>Card <?php echo esc_html( $c['code'] ); ?></span></footer>
</main>

<div class="toast" id="toast" role="status" aria-live="polite"></div>
<script><?php echo file_get_contents( SHRISHTI_CARDS_DIR . 'assets/card.js' ); // phpcs:ignore ?></script>
</body>
</html>
