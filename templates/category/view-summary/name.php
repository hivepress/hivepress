<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<h4 class="hp-category__name"><a href="<?php echo esc_url( $category->get_url() ); ?>"><?php echo esc_html( $category->get_name() ); ?></a></h4>
