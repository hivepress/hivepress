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

		if ( is_null( $post ) || hp\prefix( static::_get_name() ) !== $post['post_type'] ) {
			return;
		}

		// Get post meta.
		$meta = array_map(
			function( $values ) {
				return reset( $values );
			},
			get_post_meta( $post['ID'] )
		);

		// Get object attributes.
		$attributes = [];

		foreach ( array_keys( $this->fields ) as $field_name ) {
			if ( in_array( $field_name, $this->aliases, true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $post, array_search( $field_name, $this->aliases, true ) );
			} elseif ( ! in_array( $field_name, $this->relations, true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
			} else {
				$taxonomy = array_search( $field_name, $this->relations, true );
				$term_ids = hivepress()->cache->get_post_cache( $post['ID'], [ 'fields' => 'ids' ], $taxonomy );

				if ( is_null( $term_ids ) ) {
					$term_ids = wp_get_post_terms( $post['ID'], hp\prefix( $taxonomy ), [ 'fields' => 'ids' ] );

					if ( is_array( $term_ids ) && count( $term_ids ) <= 100 ) {
						hivepress()->cache->set_post_cache( $post['ID'], [ 'fields' => 'ids' ], $taxonomy, $term_ids );
					}
				}

				$attributes[ $field_name ] = $term_ids;
			}
		}

		return ( new static() )->set_id( $post['ID'] )->fill( $attributes );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Get post data.
		$post  = [];
		$meta  = [];
		$terms = [];

		foreach ( $this->fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->aliases, true ) ) {
					$post[ array_search( $field_name, $this->aliases, true ) ] = $field->get_value();
				} elseif ( in_array( $field_name, $this->relations, true ) ) {
					$terms[ array_search( $field_name, $this->relations, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

			// Create or update post.
			if ( is_null( $this->id ) ) {
				$id = wp_insert_post( array_merge( $post, [ 'post_type' => hp\prefix( static::_get_name() ) ] ) );

				if ( $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( ! wp_update_post( array_merge( $post, [ 'ID' => $this->id ] ) ) ) {
				return false;
			}

			// Update post meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				update_post_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
			}

			// Update post terms.
			foreach ( $terms as $taxonomy => $term_ids ) {
				wp_set_post_terms( $this->id, (array) $term_ids, hp\prefix( $taxonomy ) );
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

		return $id && wp_delete_post( absint( $id ), true );
	}
}
