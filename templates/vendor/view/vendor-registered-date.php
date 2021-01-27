<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-vendor__registered-date hp-vendor__date hp-meta" datetime="<?php echo esc_attr( $vendor->get_registered_date() ); ?>">
	<?php
	/* translators: %s: date. */
	printf( esc_html__( 'Member since %s', 'hivepress' ), $vendor->display_registered_date() );
	?>
</time>
