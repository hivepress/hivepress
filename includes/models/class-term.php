<?php
/**
 * Term model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Term model class.
 *
 * @class Term
 */
abstract class Term extends Model {

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$data = [];
		$meta = [];

		// Get field values.
		foreach ( $this->get_fields() as $field_name => $field ) {
			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->get_aliases(), true ) ) {
					$data[ array_search( $field_name, $this->get_aliases(), true ) ] = $field->get_value();
				} else {
					$meta[ hp_prefix( $field_name ) ] = $field->get_value();
				}
			} else {
				$this->errors = array_merge( $this->errors, $field->get_errors() );
			}
		}

		// Create or update term.
		if ( empty( $this->errors ) ) {
			if ( $this->get_id() === null ) {
				$this->id = wp_insert_term( uniqid(), hp_prefix( $this->get_name() ), $data );

				if ( is_wp_error( $this->id ) ) {
					$this->id = null;

					return false;
				}
			} elseif ( is_wp_error( wp_update_term( $this->get_id(), hp_prefix( $this->get_name() ), $data ) ) ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_term_meta( $this->get_id(), $meta_key, $meta_value );
			}

			return true;
		}

		return false;
	}

	/**
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	public function delete() {
		return $this->get_id() && wp_delete_term( $this->get_id(), hp_prefix( $this->get_name() ) ) !== false;
	}
}
