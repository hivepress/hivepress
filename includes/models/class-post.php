<?php
/**
 * Post model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Post model class.
 *
 * @class Post
 */
abstract class Post extends Model {

	/**
	 * Model relations.
	 *
	 * @var array
	 */
	protected static $relations = [];

	/**
	 * Gets model relations.
	 *
	 * @return array
	 */
	final public static function get_relations() {
		return static::$relations;
	}

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	final public static function get_by_id( $id ) {

		// Get instance data.
		$data = get_post( absint( $id ), ARRAY_A );

		if ( ! is_null( $data ) && hp\prefix( static::get_name() ) === $data['post_type'] ) {
			$attributes = [];

			// Get instance meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_post_meta( $data['ID'] )
			);

			// Get instance attributes.
			foreach ( array_keys( static::$fields ) as $field_name ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$attributes[ $field_name ] = hp\get_array_value( $data, array_search( $field_name, static::$aliases, true ) );
				} elseif ( ! in_array( $field_name, static::$relations, true ) ) {
					$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
				} else {
					$taxonomy = hp\prefix( array_search( $field_name, static::$relations, true ) );
					$term_ids = hivepress()->cache->get_post_cache( $data['ID'], [ 'fields' => 'ids' ], 'term/' . hp\unprefix( $taxonomy ) );

					if ( is_null( $term_ids ) ) {
						$term_ids = wp_get_post_terms( $data['ID'], $taxonomy, [ 'fields' => 'ids' ] );

						if ( is_array( $term_ids ) && count( $term_ids ) <= 100 ) {
							hivepress()->cache->set_post_cache( $data['ID'], [ 'fields' => 'ids' ], 'term/' . hp\unprefix( $taxonomy ), $term_ids );
						}
					}

					$attributes[ $field_name ] = $term_ids;
				}
			}

			// Create and fill instance.
			$instance = new static();

			$instance->set_id( $data['ID'] );
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
		return boolval( wp_delete_post( $id, true ) );
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance attributes.
		$data  = [];
		$meta  = [];
		$terms = [];

		foreach ( static::$fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$data[ array_search( $field_name, static::$aliases, true ) ] = $field->get_value();
				} elseif ( in_array( $field_name, static::$relations, true ) ) {
					$terms[ array_search( $field_name, static::$relations, true ) ] = $field->get_value();
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
				$id = wp_insert_post( array_merge( $data, [ 'post_type' => hp\prefix( static::get_name() ) ] ) );

				if ( 0 !== $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( wp_update_post( array_merge( $data, [ 'ID' => $this->id ] ) ) === 0 ) {
				return false;
			}

			// Update instance meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				update_post_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			// Update instance terms.
			foreach ( $terms as $taxonomy => $term_ids ) {
				wp_set_post_terms( $this->id, (array) $term_ids, hp\prefix( $taxonomy ) );
			}

			return true;
		}

		return false;
	}
}
