<?php
/**
 * Comment model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment model class.
 *
 * @class Comment
 */
abstract class Comment extends Model {

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {

		// Get field values.
		$data = [];
		$meta = [];

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

		// Create or update comment.
		if ( empty( $this->errors ) ) {
			if ( $this->get_id() === null ) {
				$this->id = wp_insert_comment( $data );

				if ( $this->get_id() === false ) {
					$this->id = null;

					return false;
				}
			} elseif ( wp_update_comment( array_merge( $data, [ 'comment_ID' => $this->get_id() ] ) ) === 0 ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_comment_meta( $this->get_id(), $meta_key, $meta_value );
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
		return $this->get_id() && wp_delete_comment( $this->get_id(), true ) !== false;
	}
}
