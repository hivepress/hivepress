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

		// Render actions.
		add_action( 'storefront_header', [ $this, 'render_actions' ], 31 );

		// Render modals.
		add_action( 'wp_footer', [ $this, 'render_modals' ] );
	}

	/**
	 * Renders actions.
	 */
	public function render_actions() {
		echo ( new \HivePress\Blocks\Template( [ 'template_name' => 'actions_block' ] ) )->render();
	}

	/**
	 * Renders modals.
	 */
	public function render_modals() {
		echo ( new \HivePress\Blocks\Template( [ 'template_name' => 'modals_block' ] ) )->render();
	}
}
