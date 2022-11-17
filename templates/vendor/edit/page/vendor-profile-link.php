<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
if ( isset( $vendor_id ) ) :
?>
<a href="<?php echo esc_url( hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor_id ] ) ); ?>" class="hp-form__action hp-form__action--vendor-view-profile hp-link"><i class="hp-icon fas fa-user"></i><span><?php esc_html_e( 'View Profile', 'hivepress' ); ?></span></a>
<?php endif; ?>
