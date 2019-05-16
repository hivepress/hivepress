<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<a href="<?php echo esc_url( hivepress()->router->get_url( 'listing/submit_category' ) ); ?>" class="hp-link"><i class="hp-icon fas fa-arrow-left"></i><span><?php esc_html_e( 'Change Category', 'hivepress' ); ?></span></a>
