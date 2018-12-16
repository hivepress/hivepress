<?php
namespace HivePress\WooCommerce;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages WooCommerce.
 *
 * @class WooCommerce
 */
class WooCommerce extends \HivePress\Component {

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );
	}

	/**
	 * Formats price.
	 *
	 * @param float $number
	 * @return string
	 */
	public function format_price( $number ) {
		$price = '';

		if ( $this->is_active() ) {
			$price = wp_strip_all_tags( wc_price( $number ) );
		}

		return $price;
	}

	/**
	 * Check if plugin is active.
	 *
	 * @return bool
	 */
	public function is_active() {
		return class_exists( '\WooCommerce' );
	}
}
