<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="hp-vendor__image">
	<a href="<?php echo esc_url( hivepress()->template->get_url( 'listing__vendor', [ $vendor->user_login ] ) ); ?>">
		<?php echo get_avatar( $vendor->ID, 150 ); ?>
	</a>
</div>
