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
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	public static function get( $id ) {

		// Get alias data.
		$alias_data = get_post( absint( $id ), ARRAY_A );

		if ( ! is_null( $alias_data ) && hp_prefix( self::get_name() ) === $alias_data['post_type'] ) {

			// Get alias meta.
			$alias_meta = get_post_meta( $alias_data['ID'] );

			// Get field values.
			foreach ( self::get_fields() as $field_name => $field ) {
				if ( in_array( $field_name, self::get_aliases(), true ) ) {
					$data[ $field_name ] = hp_get_array_value( $alias_data, array_search( $field_name, self::get_aliases(), true ) );
				} else {
					$data[ $field_name ] = hp_get_array_value( $alias_meta, hp_prefix( $field_name ) );
				}
			}

			// Create and fill instance.
			$instance = new static( $data );

			return $instance;
		}

		return null;
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {

		// Get field values.
		$data = [];
		$meta = [];

		foreach ( self::get_fields() as $field_name => $field ) {
			if ( $field->validate() ) {
				if ( in_array( $field_name, self::get_aliases(), true ) ) {
					$data[ array_search( $field_name, self::get_aliases(), true ) ] = $field->get_value();
				} else {
					$meta[ hp_prefix( $field_name ) ] = $field->get_value();
				}
			} else {
				$this->errors = array_merge( $this->get_errors, $field->get_errors() );
			}
		}

		// Create or update post.
		if ( empty( $this->errors ) ) {
			if ( $this->get_id() === null ) {
				$this->id = wp_insert_post( $data );

				if ( $this->get_id() === 0 ) {
					$this->id = null;

					return false;
				}
			} elseif ( wp_update_post( array_merge( $data, [ 'ID' => $this->get_id() ] ) ) === 0 ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_post_meta( $this->get_id(), $meta_key, $meta_value );
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
