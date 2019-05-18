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
	 * Form actions.
	 *
	 * @var string
	 */
	protected $form_actions;

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

		// todo.
		// $output .= ( new Container( [ 'blocks' => $this->form_actions ] ) )->render();

		return $output;
	}
}
