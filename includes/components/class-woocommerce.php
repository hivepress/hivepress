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

		// Check WooCommerce status.
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		if ( ! is_admin() ) {

			// Set page template.
			add_filter( 'wc_get_template', [ $this, 'set_page_template' ], 10, 2 );

			// Add menu items.
			add_filter( 'hivepress/v1/menus/account', [ $this, 'add_menu_items' ] );

			// Alter templates.
			add_filter( 'hivepress/v1/templates/account_page', [ $this, 'alter_account_page' ] );
		}
	}

	/**
	 * Sets page template.
	 *
	 * @param string $template_path Template path.
	 * @param string $template Template name.
	 * @return string
	 */
	public function set_page_template( $template_path, $template ) {
		if ( 'myaccount/my-account.php' === $template && ( is_wc_endpoint_url( 'orders' ) || is_wc_endpoint_url( 'view-order' ) ) ) {
			$template_path = HP_CORE_DIR . '/templates/woocommerce/myaccount/my-account.php';
		}

		return $template_path;
	}

	/**
	 * Adds menu items.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function add_menu_items( $menu ) {
		$menu['items']['woocommerce_orders'] = [
			'label' => esc_html__( 'My Orders', 'hivepress' ),
			'url'   => wc_get_endpoint_url( 'orders' ),
			'order' => 40,
		];

		return $menu;
	}

	/**
	 * Alters account page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_account_page( $template ) {
		if ( is_wc_endpoint_url( 'orders' ) || is_wc_endpoint_url( 'view-order' ) ) {
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'page_content' => [
							'blocks' => [
								'woocommerce_content' => [
									'type'     => 'callback',
									'callback' => 'do_action',
									'params'   => [ 'woocommerce_account_content' ],
									'order'    => 10,
								],
							],
						],
					],
				],
				'blocks'
			);
		}

		return $template;
	}
}
