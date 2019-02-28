<?php
/**
 * Post model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post model class.
 *
 * @class Post
 */
abstract class Post extends Model {

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {
		$data = [];
		$meta = [];

		// Get ID.
		if ( $this->get_id() ) {
			$data['ID'] = $this->get_id();
		}

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

		// Create or update post.
		if ( empty( $this->errors ) ) {
			$this->id = wp_insert_post( array_merge( $data, [ 'meta_input' => $meta ] ) );

			if ( $this->get_id() === 0 ) {
				$this->id = null;

				return false;
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
		return $this->get_id() && wp_delete_post( $this->get_id(), true ) !== false;
	}
}
