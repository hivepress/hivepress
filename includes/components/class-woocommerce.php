<?php
/**
 * WooCommerce component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Controllers;

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

			// Redirect account page.
			add_action( 'template_redirect', [ $this, 'redirect_account_page' ] );

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
		if ( wc_get_customer_order_count( get_current_user_id() ) > 0 ) {
			$menu['items']['woocommerce_orders'] = [
				'label' => hp\get_array_value( wc_get_account_menu_items(), 'orders' ),
				'url'   => wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ),
				'order' => 40,
			];
		}

		return $menu;
	}

	/**
	 * Redirects account page.
	 */
	public function redirect_account_page() {
		if ( ! is_user_logged_in() && is_account_page() ) {
			wp_safe_redirect( add_query_arg( 'redirect', rawurlencode( hp\get_current_url() ), Controllers\User::get_url( 'login_user' ) ) );

			exit();
		}
	}

	/**
	 * Alters account page.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_account_page( $template ) {
		if ( is_wc_endpoint_url( 'orders' ) || is_wc_endpoint_url( 'view-order' ) ) {

			// Set page title.
			add_filter( 'the_title', 'wc_page_endpoint_title' );

			// Alter page template.
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'page_container' => [
							'type' => 'container',
						],

						'page_content'   => [
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
