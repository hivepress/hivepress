<?php
/**
 * Attachment select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Attachment selection (Media Library).
 */
class Attachment_Select extends Field {

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'caption' => esc_html__( 'Select File', 'hivepress' ),
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = absint( $this->value );

		if ( empty( $this->value ) ) {
			$this->value = null;
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			$attachment_id = Models\Attachment::query()->filter(
				[
					'id__in' => (array) $this->value,
				]
			)->get_first_id();

			if ( empty( $attachment_id ) ) {
				$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_contains_invalid_value' ), $this->get_label( true ) ) );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output  = '<div ' . hp\html_attributes( $this->attributes ) . '>';
		$output .= '<div>';

		// Render attachment image.
		if ( ! is_null( $this->value ) ) {
			$output .= wp_get_attachment_image( $this->value, 'thumbnail' );
		}

		// Render remove button.
		$output .= '<a href="#" data-component="file-remove"><i class="hp-icon fas fa-times"></i></a>';

		// Render ID field.
		$output .= ( new Hidden(
			[
				'name'    => $this->name,
				'default' => $this->value,
			]
		) )->render();

		$output .= '</div>';

		// Render select button.
		$output .= ( new Button(
			[
				'label'      => $this->caption,

				'attributes' => [
					'data-component' => 'file-select',
				],
			]
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
