<?php
/**
 * Form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Form block class.
 *
 * @class Form
 */
class Form extends Block {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected $form_name;

	/**
	 * Form values.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Form footer.
	 *
	 * @var array
	 */
	protected $form_footer = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get form class.
		$form_class = '\HivePress\Forms\\' . $this->form_name;

		if ( class_exists( $form_class ) ) {
			$form_args = [];

			// Set model ID.
			if ( method_exists( $form_class, 'get_model' ) ) {
				$form_args['id'] = call_user_func( [ $this, 'get_' . $form_class::get_model() . '_id' ] );
			}

			// Set attributes.
			$form_args['attributes'] = $this->attributes;

			// Render footer.
			if ( ! empty( $this->form_footer ) ) {
				$form_args['footer'] = '';

				foreach ( hp\sort_array( $this->form_footer ) as $block_name => $block_args ) {
					$block_class = '\HivePress\Blocks\\' . $block_args['type'];

					if ( class_exists( $block_class ) ) {
						$form_args['footer'] .= ( new $block_class( hp\merge_arrays( $block_args, [ 'name' => $block_name ] ) ) )->render();
					}
				}
			}

			// Create form.
			$form = new $form_class( $form_args );

			if ( $form->get_method() === 'POST' ) {
				$form->set_values( array_merge( $this->values, $_POST ) );
			} elseif ( $form->get_method() === 'GET' ) {
				$form->set_values( array_merge( $this->values, $_GET ) );
			}

			// Render form.
			$output .= $form->render();
		}

		return $output;
	}
}
