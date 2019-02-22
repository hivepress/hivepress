<?php
/**
 * Template component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template component class.
 *
 * @class Template
 */
final class Template {

	/**
	 * Class constructor.
	 */
	public function __construct() {
		if ( ! is_admin() ) {

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_page_title' ] );

			// Set page template.
			add_filter( 'template_include', [ $this, 'set_page_template' ] );
		}
	}

	/**
	 * Sets page title.
	 *
	 * @param array $parts Title parts.
	 * @return string
	 */
	public function set_page_title( $parts ) {
		// todo.
		return $parts;
	}

	/**
	 * Sets page template.
	 *
	 * @param array $template Template file.
	 * @return string
	 */
	public function set_page_template( $template ) {
		$controllers=hivepress()->get_controllers();
		// todo.
		get_header();
		foreach(hivepress()->get_config('templates')['listings_page']['blocks'] as $block_args) {
			$block_class='\HivePress\Blocks\\'.$block_args['type'];
			$block=new $block_class($block_args);

			echo $block->render();
		}
		get_footer();
		die();

		return $template;
	}
}
