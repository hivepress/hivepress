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
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
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
				return reset( $values );
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
			} else {

				// Get post value.
				$values[ $field_name ] = hp\get_array_value( $post, $field->get_arg( '_alias' ) );
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

		// Set post data.
		$post  = [];
		$meta  = [];
		$terms = [];

		foreach ( $this->fields as $field ) {
			if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {

				// Set post terms.
				$terms[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( $field->get_arg( '_external' ) ) {

				// Set meta value.
				$meta[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} else {

				// Set post value.
				$post[ $field->get_arg( '_alias' ) ] = $field->get_value();
			}
		}

		// Create or update post.
		if ( empty( $this->id ) ) {
			$id = wp_insert_post( array_merge( $post, [ 'post_type' => static::_get_meta( 'alias' ) ] ) );

			if ( $id ) {
				$this->set_id( $id );
			} else {
				return false;
			}
		} elseif ( ! wp_update_post( array_merge( $post, [ 'ID' => $this->id ] ) ) ) {
			return false;
		}

		// Update post terms.
		foreach ( $terms as $taxonomy => $term_ids ) {
			wp_set_post_terms( $this->id, (array) $term_ids, $taxonomy );
		}

		// Update post meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( is_null( $meta_value ) ) {
				delete_post_meta( $this->id, $meta_key );
			} else {
				update_post_meta( $this->id, $meta_key, $meta_value );
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
}
