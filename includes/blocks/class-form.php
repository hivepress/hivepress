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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected $form;

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
	protected $footer = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get form class.
		$form_class = '\HivePress\Forms\\' . $this->form;

		if ( class_exists( $form_class ) ) {
			$form_args = [];

			// Set instance ID.
			if ( method_exists( $form_class, 'get_model' ) ) {
				$form_args['id'] = hp\get_array_value( $this->context, $form_class::get_model() . '_id' );
			}

			// Set attributes.
			$form_args['attributes'] = $this->attributes;

			// Render footer.
			if ( ! empty( $this->footer ) ) {
				$form_args['footer'] = ( new Container(
					[
						'context' => $this->context,
						'tag'     => false,
						'blocks'  => $this->footer,
					]
				) )->render();
			}

			// Create form.
			$form = new $form_class( $form_args );

			if ( $form::get_method() === 'POST' ) {
				$form->set_values( array_merge( $this->values, $_POST ) );
			} elseif ( $form::get_method() === 'GET' ) {
				$form->set_values( array_merge( $this->values, $_GET ) );
			}

			// Render form.
			$output .= $form->render();
		}

		return $output;
	}
}
