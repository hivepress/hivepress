<?php
/**
 * Textarea field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Textarea field class.
 *
 * @class Textarea
 */
class Textarea extends Text {

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Field settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Editor property.
	 *
	 * @var mixed
	 */
	protected $editor = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Textarea', 'hivepress' ),

				'settings' => [
					'editor' => [
						'label'   => 'todo',
						'caption' => 'todo',
						'type'    => 'checkbox',
						'order'   => 40,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'filters' => false,
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = sanitize_textarea_field( $this->value );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->editor ) {
			ob_start();

			// Render editor.
			wp_editor(
				$this->value,
				$this->name,
				[
					'textarea_rows' => 5,
					'media_buttons' => false,
					'quicktags'     => false,
					'tinymce'       => $this->editor,
				]
			);

			$output .= ob_get_contents();
			ob_end_clean();
		} else {

			// Render textarea.
			$output .= '<textarea name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->attributes ) . '>' . esc_textarea( $this->value ) . '</textarea>';
		}

		return $output;
	}
}
