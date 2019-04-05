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

			// Set attributes.
			$form_args['attributes'] = $this->attributes;

			// Create form.
			$form = new $form_class( $form_args );

			if ( $form->get_method() === 'POST' ) {
				$form->set_values( $_POST );
			} elseif ( $form->get_method() === 'GET' ) {
				$form->set_values( $_GET );
			}

			// Render form.
			$output .= $form->render();
		}

		return $output;
	}
}
