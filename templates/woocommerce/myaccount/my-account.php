<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo ( new HivePress\Blocks\Template( [ 'template' => 'user_account_page' ] ) )->render();
