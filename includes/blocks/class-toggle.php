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
	 * Toggle states.
	 *
	 * @var array
	 */
	protected $states = [];

	/**
	 * Toggle attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Active flag.
	 *
	 * @var bool
	 */
	protected $active = false;

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {
		$attributes = [];

		// Normalize icon.
		if ( $this->icon ) {
			$this->icon = (array) $this->icon;
		}

		// Set states.
		if ( $this->states ) {
			$this->icon     = array_column( $this->states, 'icon' );
			$this->captions = array_column( $this->states, 'caption' );
		}

		// Set attributes.
		if ( 'link' === $this->view ) {
			$attributes['class'] = [ 'hp-link' ];
		}

		if ( is_user_logged_in() ) {
			$attributes['href'] = '#';

			$attributes['data-component'] = 'toggle';
			$attributes['data-url']       = esc_url( $this->url );

			if ( $this->active ) {
				$attributes['data-icon']    = hp\get_first_array_value( $this->icon );
				$attributes['data-caption'] = hp\get_first_array_value( $this->captions );
				$attributes['data-state']   = 'active';

				if ( 'icon' === $this->view ) {
					$attributes['title'] = hp\get_last_array_value( $this->captions );
				}
			} else {
				$attributes['data-icon']    = hp\get_last_array_value( $this->icon );
				$attributes['data-caption'] = hp\get_last_array_value( $this->captions );

				if ( 'icon' === $this->view ) {
					$attributes['title'] = hp\get_first_array_value( $this->captions );
				}
			}
		} else {
			$attributes['href'] = '#user_login_modal';
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<a ' . hp\html_attributes( $this->attributes ) . '>';

		if ( $this->icon ) {

			// Get icon.
			$icon = null;

			if ( $this->active ) {
				$icon = hp\get_last_array_value( $this->icon );
			} else {
				$icon = hp\get_first_array_value( $this->icon );
			}

			// Render icon.
			$output .= '<i class="hp-icon fas fa-' . esc_attr( $icon ) . '"></i>';
		}

		if ( 'icon' !== $this->view ) {

			// Get caption.
			$caption = null;

			if ( $this->active ) {
				$caption = hp\get_last_array_value( $this->captions );
			} else {
				$caption = hp\get_first_array_value( $this->captions );
			}

			// Render caption.
			$output .= '<span>' . esc_html( $caption ) . '</span>';
		}

		$output .= '</a>';

		return $output;
	}
}
