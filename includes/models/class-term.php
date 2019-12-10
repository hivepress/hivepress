<?php
/**
 * Term model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Term model class.
 *
 * @class Term
 */
abstract class Term extends Model {

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	final public static function get_by_id( $id ) {
		return static::get_by_object( get_term( $id, hp\prefix( static::get_name() ) ) );
	}

	/**
	 * Gets instance by object.
	 *
	 * @param object $object Object.
	 * @return mixed
	 */
	final public static function get_by_object( $object ) {

		// Get instance data.
		$data = get_object_vars( $object );

		if ( ! is_null( $data ) && hp\prefix( static::get_name() ) === $data['taxonomy'] ) {
			$attributes = [];

			// Get instance meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_term_meta( $data['term_id'] )
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

			$instance->set_id( $data['term_id'] );
			$instance->fill( $attributes );

			return $instance;
		}
	}

	/**
	 * Deletes instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return bool
	 */
	final public static function delete_by_id( $id ) {
		return boolval( wp_delete_term( $id, hp\prefix( static::get_name() ) ) );
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
				$ids = wp_insert_term( uniqid(), hp\prefix( static::get_name() ), $data );

				if ( ! is_wp_error( $ids ) ) {
					$this->set_id( reset( $ids ) );
				} else {
					return false;
				}
			} elseif ( is_wp_error( wp_update_term( $this->id, hp\prefix( static::get_name() ), $data ) ) ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_term_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			return true;
		}

		return false;
	}
}
