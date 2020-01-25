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
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'type'  => 'term',
				'alias' => hp\prefix( hp\get_class_name( static::class ) ),
			],
			$meta
		);

		parent::init( $meta );
	}

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
			$term = get_term( absint( $id ), static::_get_meta( 'alias' ), ARRAY_A );
		}

		if ( empty( $term ) || is_wp_error( $term ) || static::_get_meta( 'alias' ) !== $term['taxonomy'] ) {
			return;
		}

		// Get term meta.
		$meta = array_map(
			function( $values ) {
				return reset( $values );
			},
			get_term_meta( $term['term_id'] )
		);

		// Create object.
		$object = ( new static() )->set_id( $term['term_id'] );

		// Get field values.
		$values = [];

		foreach ( $object->_get_fields() as $field_name => $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field->get_arg( '_alias' ) );
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Get term value.
				$values[ $field_name ] = hp\get_array_value( $term, $field->get_arg( '_alias' ) );
			}
		}

		return $object->fill( $values );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Validate fields.
		if ( ! $this->validate() ) {
			return false;
		}

		// Get term data.
		$term = [];
		$meta = [];

		foreach ( $this->fields as $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Set meta value.
				$meta[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Set term value.
				$term[ $field->get_arg( '_alias' ) ] = $field->get_value();
			}
		}

		// Create term.
		if ( empty( $this->id ) ) {
			$ids = wp_insert_term( uniqid(), static::_get_meta( 'alias' ), $term );

			if ( ! is_wp_error( $ids ) ) {
				$this->set_id( reset( $ids ) );
			} else {
				return false;
			}
		}

		// Update term meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_value, [ null, false ], true ) ) {
				delete_term_meta( $this->id, $meta_key );
			} else {
				update_term_meta( $this->id, $meta_key, $meta_value );
			}
		}

		// Update term.
		if ( is_wp_error( wp_update_term( $this->id, static::_get_meta( 'alias' ), $term ) ) ) {
			return false;
		}

		return true;
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

		return $id && wp_delete_term( absint( $id ), static::_get_meta( 'alias' ) );
	}
}
