<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( $listing_category_url ); ?>"><?php echo esc_html( $listing_category->get_name() ); ?></a>
