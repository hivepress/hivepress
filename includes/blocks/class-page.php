<?php
/**
 * Page block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Page block class.
 *
 * @class Page
 */
class Page extends Container {

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

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-page', 'site-main' ],
			]
		);

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
			case 'twentyseventeen':
				$output = '<div class="wrap"><div class="content-area">' . $output . '</div></div>';

				break;

			case 'twentynineteen':
				$output = '<div class="entry"><div class="entry-content">' . $output . '</div></div>';

				break;

			default:
				$output = '<div class="content-area">' . $output . '</div>';

				break;
		}

		// Add header.
		ob_start();
		get_header();
		$output = ob_get_contents() . $output;
		ob_end_clean();

		// Add footer.
		ob_start();
		get_footer();
		$output .= ob_get_contents();
		ob_end_clean();

		return $output;
	}
}
