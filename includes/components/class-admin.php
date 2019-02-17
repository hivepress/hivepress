<?php
/**
 * Admin component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin component class.
 *
 * @class Admin
 */
final class Admin {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( is_admin() ) {

			// Manage admin pages.
			add_action( 'admin_menu', [ $this, 'add_pages' ] );
			add_filter( 'custom_menu_order', [ $this, 'order_pages' ] );
			add_filter( 'menu_order', [ $this, 'order_pages' ] );
		}
	}

	/**
	 * Adds admin pages.
	 */
	public function add_pages() {
		global $menu;

		// Add separator.
		$menu[] = [ '', 'manage_options', 'hp_separator', '', 'wp-menu-separator' ];

		// Add pages.
		add_menu_page( sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), HP_CORE_NAME, 'manage_options', 'hp_settings', [ $this, 'render_settings' ], HP_CORE_URL . '/assets/images/logo.svg' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Settings', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Settings', 'hivepress' ), 'manage_options', 'hp_settings' );
		add_submenu_page( 'hp_settings', sprintf( esc_html__( '%s Add-ons', 'hivepress' ), HP_CORE_NAME ), esc_html__( 'Add-ons', 'hivepress' ), 'manage_options', 'hp_addons', [ $this, 'render_addons' ] );
	}

	/**
	 * Orders admin pages.
	 *
	 * @param array $menu Menu items.
	 * @return array
	 */
	public function order_pages( $menu ) {
		if ( current_user_can( 'manage_options' ) ) {
			if ( is_array( $menu ) ) {

				// Get admin pages.
				$pages = [
					'hp_separator',
					'hp_settings',
				];

				// Filter menu items.
				$menu = array_filter(
					$menu,
					function( $name ) use ( $pages ) {
						return ! in_array( $name, $pages, true );
					}
				);

				// Insert menu items.
				array_splice( $menu, array_search( 'separator2', $menu, true ) - 1, 0, $pages );

				return $menu;
			} else {
				return true;
			}
		}
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name Function name.
	 * @param array  $args Function arguments.
	 */
	public function __call( $name, $args ) {

		// Render admin page.
		if ( strpos( $name, 'render_' ) === 0 ) {
			$template_name = str_replace( '_', '-', str_replace( 'render_', '', $name ) );
			$template_path = HP_CORE_PATH . '/templates/admin/' . $template_name . '.php';

			if ( file_exists( $template_path ) ) {
				include $template_path;
			}
		}
	}
}
