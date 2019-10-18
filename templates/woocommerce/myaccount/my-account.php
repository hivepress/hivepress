<?php
// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

echo ( new HivePress\Blocks\Template( [ 'template' => 'account_page' ] ) )->render();
