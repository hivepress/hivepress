<?php
/**
 * WooCommerce component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce component class.
 *
 * @class WooCommerce
 */
final class WooCommerce {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Check WooCommerce.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! is_admin() ) {

			// Add menu items.
			add_filter( 'hivepress/v1/menus/account', [ $this, 'add_menu_items' ] );
		}
	}

	/**
	 * Adds menu items.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function add_menu_items( $menu ) {

		// Get page ID.
		$page_id = wc_get_page_id( 'myaccount' );

		// Add menu item.
		if ( ! empty( $page_id ) ) {
			$menu['items']['woocommerce_orders'] = [
				'label' => esc_html__( 'My Orders', 'hivepress' ),
				'url'   => get_permalink( $page_id ),
				'order' => 40,
			];
		}

		return $menu;
	}
}
