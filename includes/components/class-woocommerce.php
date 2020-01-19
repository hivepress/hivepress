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
final class WooCommerce extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Check WooCommerce status.
		if ( ! hp\is_plugin_active( 'woocommerce' ) ) {
			return;
		}

		if ( ! is_admin() ) {

			// Set account template.
			add_filter( 'wc_get_template', [ $this, 'set_account_template' ], 10, 2 );

			// Redirect account page.
			add_action( 'template_redirect', [ $this, 'redirect_account_page' ] );

			// Alter account menu.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_account_menu' ] );

			// Alter account page.
			add_filter( 'hivepress/v1/templates/user_account_page', [ $this, 'alter_account_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Gets order product IDs.
	 *
	 * @param WC_Order $order Order object.
	 * @return array
	 */
	public function get_order_product_ids( $order ) {
		return array_map(
			function( $item ) {
				return $item->get_product_id();
			},
			$order->get_items()
		);
	}

	/**
	 * Gets product price text.
	 *
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function get_product_price_text( $product ) {
		return wp_strip_all_tags( wc_price( $product->get_price() ) );
	}

	/**
	 * Sets account template.
	 *
	 * @param string $path Template path.
	 * @param string $name Template name.
	 * @return string
	 */
	public function set_account_template( $path, $name ) {
		if ( 'myaccount/my-account.php' === $name && ( is_wc_endpoint_url( 'orders' ) || is_wc_endpoint_url( 'view-order' ) ) ) {
			$path = hivepress()->get_path() . '/templates/woocommerce/myaccount/my-account.php';
		}

		return $path;
	}

	/**
	 * Redirects account page.
	 */
	public function redirect_account_page() {
		if ( ! is_user_logged_in() && is_account_page() ) {
			wp_safe_redirect(
				hivepress()->router->get_url(
					'user_login_page',
					[
						'redirect' => hivepress()->router->get_current_url(),
					]
				)
			);

			exit;
		}
	}

	/**
	 * Alters account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_account_menu( $menu ) {
		if ( wc_get_customer_order_count( get_current_user_id() ) ) {
			$menu['items']['orders_view'] = [
				'label'  => hp\get_array_value( wc_get_account_menu_items(), 'orders' ),
				'url'    => wc_get_endpoint_url( 'orders', '', wc_get_page_permalink( 'myaccount' ) ),
				'_order' => 40,
			];
		}

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
									'_order'   => 10,
								],
							],
						],
					],
				]
			);
		}

		return $template;
	}
}
