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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Set class.
		$class = [ 'hp-page' ];

		switch ( get_template() ) {
			case 'storefront':
				$class[] = 'site-main';

				break;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, [ 'class' => $class ] );

		parent::bootstrap();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = parent::render();

		// Add container.
		switch ( get_template() ) {
			case 'twentynineteen':
				$output = '<div class="entry"><div class="entry-content">' . $output . '</div></div>';

				break;

			case 'storefront':
				$output = '<div class="content-area">' . $output . '</div>';

				break;
		}

		return $output;
	}
}
