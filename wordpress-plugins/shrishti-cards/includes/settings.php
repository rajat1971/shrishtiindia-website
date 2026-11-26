<?php
/**
 * Settings → Smart Cards: company details shared by every card (spec §5).
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'admin_init',
	function () {
		register_setting(
			'shrishti_cards',
			'shrishti_cards_company',
			array(
				'type'              => 'array',
				'sanitize_callback' => 'shrishti_cards_sanitize_company',
				'show_in_rest'      => false,
			)
		);
	}
);

add_action(
	'admin_menu',
	function () {
		add_options_page( 'Smart Cards', 'Smart Cards', 'manage_options', 'shrishti-cards', 'shrishti_cards_settings_page' );
	}
);

function shrishti_cards_sanitize_company( $in ): array {
	$in  = is_array( $in ) ? $in : array();
	$out = array();
	foreach ( array( 'company_name', 'brand_name', 'header_line1', 'header_line2', 'street', 'city', 'region', 'zip', 'country', 'hours' ) as $k ) {
		$out[ $k ] = sanitize_text_field( (string) ( $in[ $k ] ?? '' ) );
	}
	foreach ( array( 'office_phones', 'group', 'vcard_note' ) as $k ) {
		$out[ $k ] = sanitize_textarea_field( (string) ( $in[ $k ] ?? '' ) );
	}
	$out['email']            = sanitize_email( (string) ( $in['email'] ?? '' ) );
	$out['website']          = esc_url_raw( (string) ( $in['website'] ?? '' ), array( 'https', 'http' ) );
	$out['map_url']          = esc_url_raw( (string) ( $in['map_url'] ?? '' ), array( 'https', 'http' ) );
	$out['credentials']      = wp_kses( (string) ( $in['credentials'] ?? '' ), array( 'b' => array(), 'strong' => array() ) );
	$out['logo_id']          = absint( $in['logo_id'] ?? 0 );
	$out['default_photo_id'] = absint( $in['default_photo_id'] ?? 0 );

	// Company details appear on every card: purge them all.
	foreach ( get_posts( array( 'post_type' => 'shrishti_card', 'numberposts' => -1, 'fields' => 'ids', 'post_status' => 'any' ) ) as $id ) {
		shrishti_cards_purge( (string) get_post_meta( $id, '_sc_code', true ) );
	}
	return $out;
}

function shrishti_cards_settings_page(): void {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$co   = shrishti_cards_company();
	$name = function ( $k ) {
		return 'shrishti_cards_company[' . $k . ']';
	};
	$row  = function ( $k, $label, $help = '', $type = 'text' ) use ( $co, $name ) {
		echo '<tr><th scope="row"><label for="co-' . esc_attr( $k ) . '">' . esc_html( $label ) . '</label></th><td>';
		if ( 'textarea' === $type ) {
			echo '<textarea id="co-' . esc_attr( $k ) . '" name="' . esc_attr( $name( $k ) ) . '" rows="3" class="large-text">' . esc_textarea( (string) $co[ $k ] ) . '</textarea>';
		} else {
			echo '<input id="co-' . esc_attr( $k ) . '" type="' . esc_attr( $type ) . '" name="' . esc_attr( $name( $k ) ) . '" value="' . esc_attr( (string) $co[ $k ] ) . '" class="regular-text">';
		}
		if ( $help ) {
			echo '<p class="description">' . esc_html( $help ) . '</p>';
		}
		echo '</td></tr>';
	};
	?>
	<div class="wrap">
		<h1>Smart Cards: company details</h1>
		<p>These details appear on every team member's card. Personal details are edited under <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shrishti_card' ) ); ?>">Smart Cards</a>.</p>
		<form method="post" action="options.php">
			<?php settings_fields( 'shrishti_cards' ); ?>
			<table class="form-table sc-form" role="presentation"><tbody>
				<?php
				$row( 'company_name', 'Company name', 'Legal name: card footer and vCard.' );
				$row( 'brand_name', 'Brand line', 'Shown under each designation.' );
				$row( 'header_line1', 'Header text, line 1' );
				$row( 'header_line2', 'Header text, line 2' );
				echo '<tr><th scope="row">Logo</th><td>';
				shrishti_cards_image_field( $name( 'logo_id' ), (int) $co['logo_id'], 'Choose logo' );
				echo '<p class="description">Light logo for the emerald header. Empty: the bundled cream Shrishti script logo.</p></td></tr>';
				$row( 'office_phones', 'Office phones', 'One per line. The first two go into the vCard.', 'textarea' );
				$row( 'email', 'Office email', 'Shown on the "no longer active" page.', 'email' );
				$row( 'street', 'Street address' );
				$row( 'city', 'City' );
				$row( 'region', 'State' );
				$row( 'zip', 'PIN code' );
				$row( 'country', 'Country' );
				$row( 'map_url', 'Google Maps link', '', 'url' );
				$row( 'hours', 'Office hours' );
				$row( 'website', 'Website', '', 'url' );
				$row( 'group', 'The Shrishti group', 'One per line: Name | Short description | URL', 'textarea' );
				$row( 'credentials', 'Credentials line', 'Use <b>…</b> for bold.' );
				$row( 'vcard_note', 'vCard note', 'Saved in the contact\'s notes field.', 'textarea' );
				echo '<tr><th scope="row">Default photo</th><td>';
				shrishti_cards_image_field( $name( 'default_photo_id' ), (int) $co['default_photo_id'] );
				echo '<p class="description">Used when a card has no photo. Empty: initials monogram.</p></td></tr>';
				?>
			</tbody></table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}
