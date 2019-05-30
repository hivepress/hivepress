<?php
/**
 * Toggle block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Toggle block class.
 *
 * @class Toggle
 */
class Toggle extends Block {

	/**
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Toggle view.
	 *
	 * @var string
	 */
	protected $view = 'link';

	/**
	 * Toggle icon.
	 *
	 * @var string
	 */
	protected $icon;

	/**
	 * Toggle URL.
	 *
	 * @var string
	 */
	protected $url;

	/**
	 * Toggle captions.
	 *
	 * @var array
	 */
	protected $captions = [];

	/**
	 * Toggle attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Active property.
	 *
	 * @var bool
	 */
	protected $active = false;

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set attributes.
		if ( 'link' === $this->view ) {
			$attributes['class'] = [ 'hp-link' ];
		}

		if ( is_user_logged_in() ) {
			$attributes['href'] = '#';

			$attributes['data-component'] = 'toggle';
			$attributes['data-url']       = $this->url;

			if ( $this->active ) {
				$attributes['data-caption'] = reset( $this->captions );
				$attributes['data-state']   = 'active';

				if ( 'icon' === $this->view ) {
					$attributes['title'] = end( $this->captions );
				}
			} else {
				$attributes['data-caption'] = end( $this->captions );

				if ( 'icon' === $this->view ) {
					$attributes['title'] = reset( $this->captions );
				}
			}
		} else {
			$attributes['href'] = '#user_login_modal';
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<a ' . hp\html_attributes( $this->attributes ) . '>';

		if ( ! is_null( $this->icon ) ) {
			$output .= '<i class="hp-icon fas fa-' . esc_attr( $this->icon ) . '"></i>';
		}

		if ( 'icon' !== $this->view ) {
			$output .= '<span>';

			if ( $this->active ) {
				$output .= esc_html( end( $this->captions ) );
			} else {
				$output .= esc_html( reset( $this->captions ) );
			}

			$output .= '</span>';
		}

		$output .= '</a>';

		return $output;
	}
}
