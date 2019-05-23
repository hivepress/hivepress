<?php
/**
 * Page container block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Page container block class.
 *
 * @class Page_Container
 */
class Page_Container extends Container {

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		switch ( get_template() ) {
			case 'storefront':
				$attributes['class'] = [ 'site-main' ];

				break;
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
		$output = parent::render();

		switch ( get_template() ) {
			case 'storefront':
				$output = '<div class="content-area">' . $output . '</div>';

				break;
		}

		return $output;
	}
}
