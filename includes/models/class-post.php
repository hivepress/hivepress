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
	final public static function get( $id ) {

		// Get alias data.
		$data = get_post( absint( $id ), ARRAY_A );

		if ( ! is_null( $data ) && hp_prefix( self::$name ) === $data['post_type'] ) {
			$values = [];

			// Get alias meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_post_meta( $data['ID'] )
			);

			// Get instance values.
			foreach ( array_keys( self::$fields ) as $field_name ) {
				if ( in_array( $field_name, self::$aliases, true ) ) {
					$values[ $field_name ] = hp_get_array_value( $data, array_search( $field_name, self::$aliases, true ) );
				} else {
					$values[ $field_name ] = hp_get_array_value( $meta, hp_prefix( $field_name ) );
				}
			}

			// Create and fill instance.
			$instance = new static();

			$instance->set_id( $data['ID'] );
			$instance->fill( $values );

			return $instance;
		}

		return null;
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance values.
		$data = [];
		$meta = [];

		foreach ( self::$fields as $field_name => $field ) {
			$field->set_value( hp_get_array_value( $this->values, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, self::$aliases, true ) ) {
					$data[ array_search( $field_name, self::$aliases, true ) ] = $field->get_value();
				} else {
					$meta[ hp_prefix( $field_name ) ] = $field->get_value();
				}
			} else {
				$this->add_errors( $field->get_errors() );
			}
		}

		// Create or update instance.
		if ( empty( $this->errors ) ) {
			if ( is_null( $this->id ) ) {
				$id = wp_insert_post( array_merge( $data, [ 'post_type' => hp_prefix( self::$name ) ] ) );

				if ( 0 !== $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( wp_update_post( array_merge( $data, [ 'ID' => $this->id ] ) ) === 0 ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_post_meta( $this->id, $meta_key, $meta_value );
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
	final public function delete() {
		return $this->id && wp_delete_post( $this->id, true ) !== false;
	}
}
