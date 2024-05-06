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
 * Post.
 */
abstract class Post extends Model {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'type'  => 'post',
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

		// Get post.
		$post = null;

		if ( is_object( $id ) ) {
			$post = get_object_vars( $id );
		} else {
			$post = get_post( absint( $id ), ARRAY_A );
		}

		if ( empty( $post ) || static::_get_meta( 'alias' ) !== $post['post_type'] ) {
			return;
		}

		// Get post meta.
		$meta = array_map(
			function( $values ) {
				return maybe_unserialize( hp\get_first_array_value( $values ) );
			},
			get_post_meta( $post['ID'] )
		);

		// Create object.
		$object = ( new static() )->set_id( $post['ID'] );

		// Get field values.
		$values = [];

		foreach ( $object->_get_fields() as $field_name => $field ) {
			if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {

				// Get cache group.
				$cache_group = hivepress()->model->get_cache_group( 'term', $field->get_arg( '_alias' ) );

				// Get post terms.
				$term_ids = hivepress()->cache->get_post_cache( $post['ID'], [ 'fields' => 'ids' ], $cache_group );

				if ( is_null( $term_ids ) ) {
					$term_ids = wp_get_post_terms( $post['ID'], $field->get_arg( '_alias' ), [ 'fields' => 'ids' ] );

					if ( is_array( $term_ids ) && count( $term_ids ) <= 100 ) {
						hivepress()->cache->set_post_cache( $post['ID'], [ 'fields' => 'ids' ], $cache_group, $term_ids );
					}
				}

				$values[ $field_name ] = $term_ids;
			} elseif ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field->get_arg( '_alias' ) );
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Get post value.
				$values[ $field_name ] = hp\get_array_value( $post, $field->get_arg( '_alias' ) );
			}
		}

		return $object->fill( $values );
	}

	/**
	 * Saves object.
	 *
	 * @param array $names Field names.
	 * @return bool
	 */
	final public function save( $names = [] ) {

		// Validate fields.
		if ( ! $this->validate( $names ) ) {
			return false;
		}

		// Filter fields.
		$fields = $this->fields;

		if ( $names ) {
			$fields = array_filter(
				$fields,
				function( $field ) use ( $names ) {
					return in_array( $field->get_name(), $names, true );
				}
			);
		}

		// Set post data.
		$post  = [];
		$meta  = [];
		$terms = [];

		foreach ( $fields as $field ) {
			if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {

				// Set post terms.
				$terms[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( $field->get_arg( '_external' ) ) {

				// Set meta value.
				$meta[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Set post value.
				$post[ $field->get_arg( '_alias' ) ] = $field->get_value();
			}
		}

		foreach ( [ 'post_excerpt', 'post_content' ] as $field_name ) {
			if ( array_key_exists( $field_name, $post ) && is_null( $post[ $field_name ] ) ) {
				$post[ $field_name ] = '';
			}
		}

		// Create post.
		$created = false;

		if ( empty( $this->id ) ) {
			$id = wp_insert_post( array_merge( $post, [ 'post_type' => static::_get_meta( 'alias' ) ] ) );

			if ( $id ) {
				$this->set_id( $id );

				$created = true;
			} else {
				return false;
			}
		}

		// Update post terms.
		foreach ( $terms as $taxonomy => $term_ids ) {
			wp_set_post_terms( $this->id, (array) $term_ids, $taxonomy );
		}

		// Update post meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_value, [ null, false ], true ) ) {
				delete_post_meta( $this->id, $meta_key );
			} else {
				update_post_meta( $this->id, $meta_key, $meta_value );
			}
		}

		// Update post.
		if ( ! $created ) {
			if ( empty( $post ) ) {

				// Fire actions.
				do_action( 'hivepress/v1/models/' . static::_get_meta( 'name' ) . '/update', $this->id, $this );
				do_action( 'hivepress/v1/models/post/update', $this->id, static::_get_meta( 'alias' ) );
			} else {
				return (bool) wp_update_post( array_merge( $post, [ 'ID' => $this->id ] ) );
			}
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

		return $id && wp_delete_post( absint( $id ), true );
	}

	/**
	 * Trashes object.
	 *
	 * @param int $id Object ID.
	 * @return bool
	 */
	final public function trash( $id = null ) {
		if ( is_null( $id ) ) {
			$id = $this->id;
		}

		return $id && wp_trash_post( absint( $id ) );
	}
}
