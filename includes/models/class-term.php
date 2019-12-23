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
	 * Gets object.
	 *
	 * @param int $id Object ID.
	 * @return mixed
	 */
	final public function get( $id ) {

		// Get term.
		$term = null;

		if ( is_object( $id ) ) {
			$term = get_object_vars( $id );
		} else {
			$term = get_term( absint( $id ), hp\prefix( static::_get_meta( 'name' ) ), ARRAY_A );
		}

		if ( is_null( $term ) || hp\prefix( static::_get_meta( 'name' ) ) !== $term['taxonomy'] ) {
			return;
		}

		// Get term meta.
		$meta = array_map(
			function( $values ) {
				return reset( $values );
			},
			get_term_meta( $term['term_id'] )
		);

		// Get object attributes.
		$attributes = [];

		foreach ( array_keys( $this->fields ) as $field_name ) {
			if ( in_array( $field_name, $this->aliases, true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $term, array_search( $field_name, $this->aliases, true ) );
			} else {
				$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
			}
		}

		return ( new static() )->set_id( $term['term_id'] )->fill( $attributes );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Get term data.
		$term = [];
		$meta = [];

		foreach ( $this->fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->aliases, true ) ) {
					$term[ array_search( $field_name, $this->aliases, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

			// Create or update term.
			if ( is_null( $this->id ) ) {
				$ids = wp_insert_term( uniqid(), hp\prefix( static::_get_meta( 'name' ) ), $term );

				if ( ! is_wp_error( $ids ) ) {
					$this->set_id( reset( $ids ) );
				} else {
					return false;
				}
			} elseif ( is_wp_error( wp_update_term( $this->id, hp\prefix( static::_get_meta( 'name' ) ), $term ) ) ) {
				return false;
			}

			// Update term meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				update_term_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			return true;
		}

		return false;
	}

	/**
	 * Deletes object.
	 *
	 * @param int $id Object ID.
	 * @return bool
	 */
	final public function delete( $id = null ) {
		if ( is_null( $id ) ) {
			$id = $this->id;
		}

		return $id && wp_delete_term( absint( $id ), hp\prefix( static::_get_meta( 'name' ) ) );
	}
}
