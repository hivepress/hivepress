<?php
/**
 * Template component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template component class.
 *
 * @class Template
 */
final class Template {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// todo.
		add_action( 'storefront_header', [ $this, 'render_menu' ], 31 );
		add_action( 'wp_footer', [ $this, 'render_modals' ] );
	}

	// todo.
	public function render_menu() {
		echo ( new \HivePress\Blocks\Template( [ 'attributes' => [ 'template_name' => 'menu' ] ] ) )->render();
	}

	public function render_modals() {
		echo ( new \HivePress\Blocks\Template( [ 'attributes' => [ 'template_name' => 'modals' ] ] ) )->render();
	}
}
