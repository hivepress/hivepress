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
 * Implements integration with WooCommerce.
 */
final class WooCommerce extends Component {

	/**
	 * WooCommerce objects.
	 *
	 * @var array
	 */
	protected $objects = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update options.
		add_action( 'activate_woocommerce/woocommerce.php', [ $this, 'update_options' ] );

		// Check WooCommerce status.
		if ( ! hp\is_plugin_active( 'woocommerce' ) ) {
			return;
		}

		// Update options.
		add_action( 'hivepress/v1/activate', [ $this, 'update_options' ] );

		// Update order status.
		add_action( 'woocommerce_order_status_changed', [ $this, 'update_order_status' ], 10, 4 );

		// Set order item meta.
		add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'set_order_item_meta' ], 10, 3 );

		// Format order item meta.
		add_filter( 'woocommerce_order_item_get_formatted_meta_data', [ $this, 'format_order_item_meta' ] );

		// Format cart item meta.
		add_filter( 'woocommerce_get_item_data', [ $this, 'format_cart_item_meta' ], 10, 2 );

		// Update user billing name.
		add_action( 'hivepress/v1/models/user/update_first_name', [ $this, 'update_user_billing_name' ], 10, 2 );
		add_action( 'hivepress/v1/models/user/update_last_name', [ $this, 'update_user_billing_name' ], 10, 2 );

		// Set countries configuration.
		add_filter( 'hivepress/v1/countries', [ $this, 'set_countries' ] );

		if ( ! is_admin() ) {

			// Set request context.
			add_filter( 'hivepress/v1/components/request/context', [ $this, 'set_request_context' ] );

			// Redirect pages.
			add_action( 'template_redirect', [ $this, 'redirect_pages' ] );

			// Set account template.
			add_filter( 'wc_get_template', [ $this, 'set_account_template' ], 10, 2 );

			// Alter account menu.
			add_filter( 'hivepress/v1/menus/user_account', [ $this, 'alter_account_menu' ] );

			// Alter account page.
			add_filter( 'hivepress/v1/templates/user_account_page', [ $this, 'alter_account_page' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Updates options.
	 */
	public function update_options() {
		update_option( 'woocommerce_enable_guest_checkout', 'no' );
		update_option( 'woocommerce_enable_checkout_login_reminder', 'yes' );
		update_option( 'woocommerce_enable_signup_and_login_from_checkout', 'yes' );

		if ( get_option( 'hp_installed_time' ) > strtotime( '2024-07-08' ) ) {

			// @todo Remove after HPOS integration.
			update_option( 'woocommerce_custom_orders_table_enabled', 'no' );
			update_option( 'woocommerce_custom_orders_table_data_sync_enabled', 'no' );
		}
	}

	/**
	 * Gets WooCommerce configuration.
	 *
	 * @param string $name Configuration name.
	 * @return array
	 */
	public function get_config( $name ) {
		return hp\get_array_value( hivepress()->get_config( 'woocommerce' ), $name, [] );
	}

	/**
	 * Gets item meta fields.
	 *
	 * @return array
	 */
	protected function get_item_meta_fields() {
		if ( ! isset( $this->objects['item_meta'] ) ) {
			$this->objects['item_meta'] = [];

			foreach ( hp\sort_array( $this->get_config( 'item_meta' ) ) as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$this->objects['item_meta'][ $name ] = $field;
				}
			}
		}

		return $this->objects['item_meta'];
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
	 * Gets related product.
	 *
	 * @param int   $parent_id Parent ID.
	 * @param array $args Query arguments.
	 * @return object
	 */
	public function get_related_product( $parent_id, $args = [] ) {
		return hp\get_first_array_value(
			wc_get_products(
				array_merge(
					[
						'parent' => $parent_id,
						'limit'  => 1,
					],
					$args
				)
			)
		);
	}

	/**
	 * Formats price.
	 *
	 * @param float $price Price.
	 * @return string
	 */
	public function format_price( $price ) {
		return wp_strip_all_tags( wc_price( $price ) );
	}

	/**
	 * Gets product price text.
	 *
	 * @param WC_Product $product Product object.
	 * @return string
	 */
	public function get_product_price_text( $product ) {
		return $this->format_price( $product->get_price() );
	}

	/**
	 * Updates order status.
	 *
	 * @param int      $order_id Order ID.
	 * @param string   $old_status Old status.
	 * @param string   $new_status New status.
	 * @param WC_Order $order Order object.
	 */
	public function update_order_status( $order_id, $old_status, $new_status, $order ) {

		// Check user.
		if ( ! $order->get_user_id() ) {
			return;
		}

		// Delete cached order count.
		hivepress()->cache->delete_user_cache( $order->get_user_id(), 'order_count' );
	}

	/**
	 * Sets order item meta.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $meta Meta values.
	 */
	public function set_order_item_meta( $item, $cart_item_key, $meta ) {

		// Get fields.
		$fields = $this->get_item_meta_fields();

		// Set meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( strpos( $meta_key, 'hp_' ) === 0 ) {

				// Get field.
				$field = hp\get_array_value( $fields, hp\unprefix( $meta_key ) );

				if ( $field ) {

					// Set value.
					$field->set_value( $meta_value );

					if ( $field->validate() ) {
						$item->update_meta_data( $meta_key, $field->get_value() );
					}
				}
			}
		}
	}

	/**
	 * Formats order item meta.
	 *
	 * @param array $meta Meta values.
	 * @return array
	 */
	public function format_order_item_meta( $meta ) {

		// Get fields.
		$fields = $this->get_item_meta_fields();

		// Filter meta.
		$meta = array_filter(
			array_map(
				function( $args ) use ( $fields ) {
					if ( strpos( $args->key, 'hp_' ) === 0 ) {

						// Get field.
						$field = hp\get_array_value( $fields, hp\unprefix( $args->key ) );

						if ( $field ) {
							if ( $field->get_label() ) {

								// Set value.
								$field->set_value( $args->value );

								// Set meta.
								$args->display_key   = $field->get_label();
								$args->display_value = '<p>' . $field->display() . '</p>';
							} else {

								// Remove meta.
								$args = null;
							}
						}
					}

					return $args;
				},
				$meta
			)
		);

		return $meta;
	}

	/**
	 * Formats cart item meta.
	 *
	 * @param array $meta Meta values.
	 * @param array $cart_item Cart item.
	 * @return array
	 */
	public function format_cart_item_meta( $meta, $cart_item ) {

		// Get fields.
		$fields = $this->get_item_meta_fields();

		foreach ( $fields as $name => $field ) {
			if ( isset( $cart_item[ hp\prefix( $name ) ] ) && $field->get_label() ) {

				// Set value.
				$field->set_value( $cart_item[ hp\prefix( $name ) ] );

				// Add meta.
				$meta[] = [
					'key'   => $field->get_label(),
					'value' => $field->display(),
				];
			}
		}

		return $meta;
	}

	/**
	 * Updates user billing name.
	 *
	 * @param int    $user_id User ID.
	 * @param string $value Value.
	 */
	public function update_user_billing_name( $user_id, $value ) {

		// Check field value.
		if ( ! $value ) {
			return;
		}

		// Get field name.
		$field_name = substr( hp\get_last_array_value( explode( '/', current_filter() ) ), strlen( 'update_' ) );

		// Update field value.
		update_user_meta( $user_id, 'billing_' . $field_name, $value );
	}

	/**
	 * Sets countries configuration.
	 *
	 * @param array $countries Countries array.
	 * @return array
	 */
	public function set_countries( $countries ) {
		return WC()->countries->get_countries();
	}

	/**
	 * Sets request context.
	 *
	 * @param array $context Request context.
	 * @return array
	 */
	public function set_request_context( $context ) {

		// Get cached order count.
		$order_count = hivepress()->cache->get_user_cache( get_current_user_id(), 'order_count' );

		if ( is_null( $order_count ) ) {

			// Get order count.
			$order_count = wc_get_customer_order_count( get_current_user_id() );

			// Cache order count.
			hivepress()->cache->set_user_cache( get_current_user_id(), 'order_count', null, $order_count );
		}

		// Set request context.
		$context['order_count'] = $order_count;

		return $context;
	}

	/**
	 * Redirects pages.
	 */
	public function redirect_pages() {
		$url = null;

		// Redirect account page.
		if ( ! is_user_logged_in() && is_account_page() ) {
			$url = hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Redirect product page.
		if ( is_product() ) {
			$parent = get_post_parent();

			if ( $parent && strpos( $parent->post_type, 'hp_' ) === 0 ) {
				$url = get_permalink( $parent->ID );
			}
		}

		if ( $url ) {
			wp_safe_redirect( $url );

			exit;
		}
	}

	/**
	 * Sets account page template.
	 *
	 * @param string $path Template filepath.
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
	 * Alters account menu.
	 *
	 * @param array $menu Menu arguments.
	 * @return array
	 */
	public function alter_account_menu( $menu ) {
		if ( hivepress()->request->get_context( 'order_count' ) ) {
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
