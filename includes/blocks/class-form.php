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
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get form class.
		$form_class = '\HivePress\Forms\\' . $this->form_name;

		if ( class_exists( $form_class ) ) {

			// Create form.
			// todo.
			$form = new $form_class( $this->values );

			$form->set_values( $_GET );

			// Render form.
			$output .= $form->render();
		}

		return $output;
	}
}
