<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<time class="hp-vendor__registered-date hp-meta" datetime="<?php echo esc_attr( $user->get_registered_date() ); ?>">
	<?php
	/* translators: %s: date. */
	printf( esc_html__( 'Member since %s', 'hivepress' ), $user->display_registered_date() );
	?>
</time>
