<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo ( new HivePress\Blocks\Template( [ 'template' => 'user_account_page' ] ) )->render(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Block HTML, blocks escape their own output.
