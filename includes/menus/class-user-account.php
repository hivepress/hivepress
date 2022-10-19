<?php
/**
 * User account menu.
 *
 * @package HivePress\Menus
 */

namespace HivePress\Menus;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User account pages.
 */
class User_Account extends Menu {

	/**
	 * Menu mode.
	 *
	 * @var string
	 */
	protected $mode = 'account';

	/**
	 * Class constructor.
	 *
	 * @param array $args Menu arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'items' => [
					'user_edit_settings' => [
						'route'  => 'user_edit_settings_page',
						'_order' => 50,
					],

					'user_logout'        => [
						'label'  => esc_html__( 'Sign Out', 'hivepress' ),
						'url'    => hivepress()->router->get_url( 'user_logout_page' ),
						'_order' => 1000,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Renders menu HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->items ) {

			// Render items.
			$output .= $this->render_items();

			if ( 'account' === $this->mode ) {
				$output = '<nav ' . hp\html_attributes( $this->attributes ) . '>' . $output . '</nav>';
			}
		}

		return $output;
	}

	/**
	 * Renders menu items.
	 *
	 * @param mixed $current Current item name.
	 * @return string
	 */
	protected function render_items( $current = null ) {
		$output = '';

		// Filter items.
		$items = array_filter(
			$this->items,
			function( $item ) use ( $current ) {
				$parent = hp\get_array_value( $item, '_parent' );

				return ( is_null( $current ) && is_null( $parent ) ) || ( ! is_null( $current ) && $parent === $current );
			}
		);

		if ( $items ) {

			// Get current URL.
			$url = hivepress()->router->get_current_url();

			// Get current route.
			$route = hivepress()->router->get_current_route_name();

			// Render items.
			if ( 'account' === $this->mode ) {
				$output .= '<ul>';
			} else {
				$output .= '<ul class="sub-menu">';
			}

			foreach ( hp\sort_array( $items ) as $name => $args ) {

				// Get current class.
				$class = 'hp-menu__item--' . hp\sanitize_slug( $name );

				if ( $args['url'] === $url || hp\get_array_value( $args, 'route', false ) === $route ) {
					$class .= ' hp-menu__item--current current-menu-item';
				}

				// Render menu item.
				$output .= '<li class="hp-menu__item ' . esc_attr( $class ) . '">';
				$output .= '<a href="' . esc_url( $args['url'] ) . '">';

				// Render label.
				$output .= '<span>' . esc_html( $args['label'] ) . '</span>';

				// Render meta.
				if ( isset( $args['meta'] ) ) {
					$output .= '<small>' . esc_html( $args['meta'] ) . '</small>';
				}

				$output .= '</a>';

				// Render child items.
				$output .= $this->render_items( $name );

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}
}
