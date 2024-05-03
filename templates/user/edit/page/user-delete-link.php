<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<?php if ( get_option( 'hp_user_enable_account_deletion', true ) ) : ?>
    <a href="#user_delete_modal" class="hp-form__action hp-form__action--user-delete hp-link"><i class="hp-icon fas fa-times"></i><span><?php esc_html_e( 'Delete Account', 'hivepress' ); ?></span></a>
<?php endif; ?>