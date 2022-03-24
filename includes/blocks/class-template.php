<?php
/**
 * Template block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders a template.
 */
class Template extends Block {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Custom blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$blocks  = [];
		$context = [];

		// Get template class.
		$class = '\HivePress\Templates\\' . $this->template;

		if ( class_exists( $class ) && $class::get_meta( 'label' ) ) {

			// Get template content.
			$content = get_page_by_path( $class::get_meta( 'name' ), OBJECT, 'hp_template' );

			if ( $content && 'publish' === $content->post_status ) {

				// Register blocks.
				hivepress()->editor->register_template_blocks(
					$this->template,
					[
						'blocks'  => $this->blocks,
						'context' => $this->context,
					]
				);

				// Set blocks.
				$blocks = [
					'page_container' => [
						'type'   => 'page',
						'_order' => 10,

						'blocks' => [
							'page_content' => [
								'type'    => 'content',
								'content' => apply_filters( 'the_content', $content->post_content ),
								'_order'  => 10,
							],
						],
					],
				];
			}
		}

		if ( ! $blocks ) {

			// Create template.
			$template = hp\create_class_instance(
				$class,
				[
					[
						'blocks'  => $this->blocks,
						'context' => $this->context,
					],
				]
			);

			if ( $template ) {

				// Set blocks.
				$blocks = $template->get_blocks();

				// Set context.
				$context = $template->get_context();
			}
		}

		// Render template.
		return ( new Container(
			[
				'tag'     => false,
				'blocks'  => $blocks,
				'context' => $context,
			]
		) )->render();
	}
}
