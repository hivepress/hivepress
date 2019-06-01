<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

if ( $listing->get_status() === 'publish' ) :
	?>
	<a href="<?php the_permalink(); ?>" target="_blank" class="hp-link"><i class="hp-icon fas fa-external-link-alt"></i><span><?php esc_html_e( 'View', 'hivepress' ); ?></span></a>
	<?php
endif;
