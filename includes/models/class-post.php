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

		// Get field values.
		$values = [];

		foreach ( $this->fields as $field_name => $field ) {

			// Get field alias.
			$field_alias = hp\prefix( $field_name );

			if ( $field->get_arg( '_alias' ) ) {
				$field_alias = $field->get_arg( '_alias' );
			}

			if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {

				// Get post terms.
				$taxonomy = $field->get_arg( '_model' );
				$term_ids = hivepress()->cache->get_post_cache( $post['ID'], [ 'fields' => 'ids' ], $taxonomy );

				if ( is_null( $term_ids ) ) {
					$term_ids = wp_get_post_terms( $post['ID'], hp\prefix( $taxonomy ), [ 'fields' => 'ids' ] );

					if ( is_array( $term_ids ) && count( $term_ids ) <= 100 ) {
						hivepress()->cache->set_post_cache( $post['ID'], [ 'fields' => 'ids' ], $taxonomy, $term_ids );
					}
				}

				$values[ $field_name ] = $term_ids;
			} elseif ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field_alias );
			} else {

				// Get post value.
				$values[ $field_name ] = hp\get_array_value( $post, $field_alias );
			}
		}

		return ( new static() )->set_id( $post['ID'] )->fill( $values );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Set post values.
		$post  = [];
		$meta  = [];
		$terms = [];

		foreach ( $this->fields as $field_name => $field ) {
			if ( $field->validate() ) {

				// Get field alias.
				$field_alias = hp\prefix( $field_name );

				if ( $field->get_arg( '_alias' ) ) {
					$field_alias = $field->get_arg( '_alias' );
				}

				if ( $field->get_arg( '_relation' ) === 'many_to_many' ) {

					// Set post terms.
					$terms[ hp\prefix( $field->get_arg( '_model' ) ) ] = $field->get_value();
				} elseif ( $field->get_arg( '_external' ) ) {

					// Set meta value.
					$meta[ $field_alias ] = $field->get_value();
				} else {

					// Set post value.
					$post[ $field_alias ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

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
				update_post_meta( $this->id, $meta_key, $meta_value );
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
		if ( empty( $id ) ) {
			$id = $this->id;
		}

		return $id && wp_delete_post( absint( $id ), true );
	}
}
