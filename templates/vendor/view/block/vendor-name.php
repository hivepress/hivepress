<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-vendor__name"><a href="<?php the_permalink(); ?>"><?php echo esc_html( $vendor->get_name() ); ?></a></h4>
