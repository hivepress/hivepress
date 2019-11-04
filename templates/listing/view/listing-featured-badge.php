<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( get_post_meta( get_the_ID(), 'hp_featured', true ) ) :
	?>
	<div class="hp-listing__featured" title="<?php esc_attr_e( 'Featured', 'hivepress' ); ?>">
		<i class="hp-icon fas fa-star"></i>
	</div>
	<?php
endif;
