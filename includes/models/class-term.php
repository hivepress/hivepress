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

		// Get field values.
		$values = [];

		foreach ( $this->fields as $field_name => $field ) {

			// Get field alias.
			$field_alias = hp\prefix( $field_name );

			if ( $field->get_arg( '_alias' ) ) {
				$field_alias = $field->get_arg( '_alias' );
			}

			if ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field_alias );
			} else {

				// Get term value.
				$values[ $field_name ] = hp\get_array_value( $term, $field_alias );
			}
		}

		return ( new static() )->set_id( $term['term_id'] )->fill( $values );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Get term values.
		$term = [];
		$meta = [];

		foreach ( $this->fields as $field_name => $field ) {
			if ( $field->validate() ) {

				// Get field alias.
				$field_alias = hp\prefix( $field_name );

				if ( $field->get_arg( '_alias' ) ) {
					$field_alias = $field->get_arg( '_alias' );
				}

				if ( $field->get_arg( '_external' ) ) {

					// Set meta value.
					$meta[ $field_alias ] = $field->get_value();
				} else {

					// Set term value.
					$term[ $field_alias ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

			// Create or update term.
			if ( empty( $this->id ) ) {
				$ids = wp_insert_term( uniqid(), static::_get_meta( 'alias' ), $term );

				if ( ! is_wp_error( $ids ) ) {
					$this->set_id( reset( $ids ) );
				} else {
					return false;
				}
			} elseif ( is_wp_error( wp_update_term( $this->id, static::_get_meta( 'alias' ), $term ) ) ) {
				return false;
			}

			// Update term meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( is_null( $meta_value ) ) {
					delete_term_meta( $this->id, $meta_key );
				} else {
					update_term_meta( $this->id, $meta_key, $meta_value );
				}
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

		return $id && wp_delete_term( absint( $id ), static::_get_meta( 'alias' ) );
	}
}
