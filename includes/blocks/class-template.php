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

		// Get template count.
		$count = wp_count_posts( 'hp_template' );

		if ( $count->publish && class_exists( $class ) && $class::get_meta( 'label' ) ) {

			// Get template content.
			$content = get_page_by_path( $class::get_meta( 'name' ), OBJECT, 'hp_template' );

			if ( $content && 'publish' === $content->post_status ) {

				// Register blocks.
				$template = hivepress()->editor->register_template_blocks(
					$this->template,
					[
						'context' => $this->context,
						'blocks'  => $this->blocks,
					]
				);

				// Set blocks.
				$blocks = [
					'page_container' => [
						'type'       => 'page',
						'attributes' => $template->get_attributes(),
						'_order'     => 10,

						'blocks'     => [
							'page_content' => [
								'type'     => 'callback',
								'callback' => 'apply_filters',
								'params'   => [ 'the_content', $content->post_content ],
								'return'   => true,
								'_order'   => 10,
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
						'context' => $this->context,
						'blocks'  => $this->blocks,
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
				'context' => $context,
				'blocks'  => $blocks,
			]
		) )->render();
	}
}
