<?php
/**
 * Currency field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number with a currency symbol (WooCommerce).
 */
class Currency extends Number {

	/**
	 * Product ID.
	 *
	 * @var int
	 */
	protected $product;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label' => null,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'display_type' => 'number',
				'decimals'     => 2,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) && hp\is_plugin_active( 'woocommerce' ) ) {

			// Get price.
			$price = $this->value;

			if ( $this->product ) {

				// Get product.
				$product = wc_get_product( $this->product );

				if ( $product ) {

					// Set price.
					$price = wc_get_price_to_display( $product );
				}
			}

			return hivepress()->woocommerce->format_price( $price );
		}
	}
}
