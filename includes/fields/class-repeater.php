<?php
/**
 * Repeater field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Repeatable fields.
 */
class Repeater extends Field {

	/**
	 * Inner fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set component.
		$this->attributes['data-component'] = 'repeater';

		// Add fields.
		$fields = $this->fields;

		$this->fields = [];

		foreach ( hp\sort_array( $fields ) as $name => $args ) {
			if ( isset( $args['type'] ) ) {

				// Set name.
				$args['name'] = $this->name . '[%index%][' . $name . ']';

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ $args ] );

				// Add field.
				if ( $field ) {
					$this->fields[ $name ] = $field;
				}
			}
		}

		parent::boot();
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_null( $this->value ) && ! is_array( $this->value ) ) {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {

		// Get items.
		$items = [];

		foreach ( $this->value as $index => $item ) {
			$items[ $index ] = [];

			foreach ( $this->fields as $name => $field ) {
				$field->set_value( hp\get_array_value( $item, $name ) );

				// Add item.
				if ( ! $field->is_required() || ! is_null( $field->get_value() ) ) {
					$items[ $index ][ $name ] = $field->get_value();
				}
			}

			// Remove item.
			if ( count( $items[ $index ] ) < count( $this->fields ) ) {
				unset( $items[ $index ] );
			}
		}

		// Set value.
		if ( $items ) {
			$this->value = array_values( $items );
		} else {
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

			// Validate fields.
			$errors = [];

			foreach ( $this->value as $item ) {
				foreach ( $this->fields as $name => $field ) {
					$field->set_value( hp\get_array_value( $item, $name ) );

					if ( ! $field->validate() ) {
						$errors = array_merge( $errors, $field->get_errors() );
					}
				}
			}

			// Add errors.
			if ( $errors ) {
				$this->add_errors( array_unique( $errors ) );
			}
		}

		return ! $this->errors;
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render items.
		$output .= '<table class="hp-table"><tbody>';

		if ( ! $this->value ) {
			$this->value = [ [] ];
		}

		foreach ( $this->value as $item ) {
			$output .= '<tr>';

			// Render sort handle.
			$output .= '<td><a class="hp-link" data-sort><i class="hp-icon fas fa-bars"></i></a></td>';

			// Render fields.
			$index = uniqid();

			foreach ( $this->fields as $name => $field ) {
				$field->set_value( hp\get_array_value( $item, $name ) );

				$output .= '<td>' . str_replace( '%index%', $index, $field->render() ) . '</td>';
			}

			// Render remove button.
			$output .= '<td><a title="' . esc_attr__( 'Remove', 'hivepress' ) . '" class="hp-link" data-remove><i class="hp-icon fas fa-times"></i></a></td>';

			$output .= '</tr>';
		}

		$output .= '</tbody></table>';

		// Render add button.
		$output .= ( new Button(
			[
				'label'      => esc_html__( 'Add Item', 'hivepress' ),

				'attributes' => [
					'data-add' => 'true',
				],
			]
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
