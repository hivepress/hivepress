<?php
/**
 * Editor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Editor component class.
 *
 * @class Editor
 */
final class Editor {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', [ $this, 'enqueue_styles' ] );
		}
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		foreach ( hivepress()->get_config( 'styles' ) as $style ) {
			if ( hp_get_array_value( $style, 'editor', false ) ) {
				add_editor_style( $style['src'] );
			}
		}
	}
}
