<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-category__title"><a href="<?php echo esc_url( get_term_link( $category->term_id ) ); ?>"><?php echo esc_html( $category->name ); ?></a></h4>
