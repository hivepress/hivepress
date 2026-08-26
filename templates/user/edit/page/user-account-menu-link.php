<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<button type="button" class="button button--large hp-button hp-button--wide hp-button--mobile hp-button--block hp-button--user-account-menu" data-component="link" data-url="#user_account_menu_modal"><i class="hp-icon fas fa-user"></i><span><?php echo esc_html( hivepress()->translator->get_string( 'my_account' ) ); ?></span></button>
