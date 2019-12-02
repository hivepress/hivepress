<?php
/**
 * Comment model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment model class.
 *
 * @class Comment
 */
abstract class Comment extends Model {

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	final public static function get_by_id( $id ) {

		// Get instance data.
		$id   = absint( $id );
		$data = get_comment( $id, ARRAY_A );

		if ( ! is_null( $data ) && hp\prefix( static::get_name() ) === $data['comment_type'] ) {
			$attributes = [];

			// Get instance meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_comment_meta( $data['comment_ID'] )
			);

			// Get instance attributes.
			foreach ( array_keys( static::$fields ) as $field_name ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$attributes[ $field_name ] = hp\get_array_value( $data, array_search( $field_name, static::$aliases, true ) );
				} else {
					$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
				}
			}

			// Create and fill instance.
			$instance = new static();

			$instance->set_id( $data['comment_ID'] );
			$instance->fill( $attributes );

			return $instance;
		}

		return null;
	}

	/**
	 * Deletes instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return bool
	 */
	final public static function delete_by_id( $id ) {
		return boolval( wp_delete_comment( $id, true ) );
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance attributes.
		$data = [];
		$meta = [];

		foreach ( static::$fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$data[ array_search( $field_name, static::$aliases, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->add_errors( $field->get_errors() );
			}
		}

		// Create or update instance.
		if ( empty( $this->errors ) ) {
			if ( is_null( $this->id ) ) {
				$id = wp_insert_comment( array_merge( $data, [ 'comment_type' => hp\prefix( static::get_name() ) ] ) );

				if ( false !== $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( wp_update_comment( array_merge( $data, [ 'comment_ID' => $this->id ] ) ) === 0 ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_comment_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			return true;
		}

		return false;
	}
}
