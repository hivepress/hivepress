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
	 * Form header.
	 *
	 * @var array
	 */
	protected $header = [];

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

		// Get arguments.
		$form_args = [];

		// Set object ID.
		$model = hp\call_class_method( '\HivePress\Forms\\' . $this->form, 'get_model' );

		if ( $model ) {
			$object = hp\get_array_value( $this->context, $model );

			if ( is_object( $object ) && strtolower( get_class( $object ) ) === strtolower( 'HivePress\Models\\' . $model ) ) {
				$form_args['id'] = $object->get_id();
			}
		}

		// Set attributes.
		$form_args['attributes'] = $this->attributes;

		// Render header.
		if ( $this->header ) {
			$form_args['header'] = ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $this->header,
				]
			) )->render();
		}

		// Render footer.
		if ( $this->footer ) {
			$form_args['footer'] = ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $this->footer,
				]
			) )->render();
		}

		// Create form.
		$form = hp\create_class_instance( '\HivePress\Forms\\' . $this->form, [ $form_args ] );

		if ( ! is_null( $form ) ) {
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
