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
 * Renders a page.
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
		$output = '';

		// Render header.
		ob_start();

		get_header();
		$output .= ob_get_contents();

		ob_end_clean();

		// Query posts.
		$query = hivepress()->request->get_context( 'post_query' );

		if ( $query ) {
			$query['hp_main'] = true;

			if ( is_page() ) {
				$query['hp_archive'] = true;
			}

			query_posts( $query );
		}

		// Render content.
		$content = parent::render();

		switch ( get_template() ) {
			case 'twentyseventeen':
				$content = '<div class="wrap"><div class="content-area">' . $content . '</div></div>';

				break;

			case 'twentynineteen':
			case 'popularfx':
			case 'go':
				$content = '<div class="entry"><div class="entry-content">' . $content . '</div></div>';

				break;

			case 'astra':
			case 'inspiro':
				$content = '<div id="primary" class="content-area primary">' . $content . '</div>';

				break;

			case 'oceanwp':
			case 'neve':
				$content = '<div id="content-wrap" class="container single-page-container clr">' . $content . '</div>';

				break;

			case 'kadence':
				$content = '<div id="primary" class="content-area"><div class="content-container site-container">' . $content . '</div></div>';

				break;

			case 'blocksy':
				$content = '<div class="ct-container" data-vertical-spacing="top:bottom">' . $content . '</div>';

				break;

			case 'generatepress':
				break;

			default:
				$content = '<div class="content-area">' . $content . '</div>';

				break;
		}

		$output .= $content;

		// Render footer.
		ob_start();

		get_footer();
		$output .= ob_get_contents();

		ob_end_clean();

		return $output;
	}
}
