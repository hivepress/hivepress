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
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-page', 'site-main' ],
			]
		);

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Render header.
		ob_start();

		get_header();
		$header = ob_get_contents();

		ob_end_clean();

		// Render footer.
		ob_start();

		get_footer();
		$footer = ob_get_contents();

		ob_end_clean();

		// Query posts.
		if ( hivepress()->request->get_context( 'post_query' ) ) {
			query_posts( hivepress()->request->get_context( 'post_query' ) );
		}

		// Render content.
		$content = parent::render();

		// Add wrapper.
		switch ( get_template() ) {
			case 'twentyseventeen':
				$content = '<div class="wrap"><div class="content-area">' . $content . '</div></div>';

				break;

			case 'twentynineteen':
				$content = '<div class="entry"><div class="entry-content">' . $content . '</div></div>';

				break;

			default:
				$content = '<div class="content-area">' . $content . '</div>';

				break;
		}

		return $header . $content . $footer;
	}
}
