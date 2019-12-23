<?php
/**
 * Comment model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment model class.
 *
 * @class Comment
 */
abstract class Comment extends Model {

	/**
	 * Gets object.
	 *
	 * @param int $id Object ID.
	 * @return mixed
	 */
	final public function get( $id ) {

		// Get comment.
		$comment = null;

		if ( is_object( $id ) ) {
			$comment = get_object_vars( $id );
		} else {
			$comment = get_comment( absint( $id ), ARRAY_A );
		}

		if ( is_null( $comment ) || hp\prefix( static::_get_meta( 'name' ) ) !== $comment['comment_type'] ) {
			return;
		}

		// Get comment meta.
		$meta = array_map(
			function( $values ) {
				return reset( $values );
			},
			get_comment_meta( $comment['comment_ID'] )
		);

		// Get object attributes.
		$attributes = [];

		foreach ( array_keys( $this->fields ) as $field_name ) {
			if ( in_array( $field_name, $this->aliases, true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $comment, array_search( $field_name, $this->aliases, true ) );
			} else {
				$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
			}
		}

		return ( new static() )->set_id( $comment['comment_ID'] )->fill( $attributes );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Get comment data.
		$comment = [];
		$meta    = [];

		foreach ( $this->fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->aliases, true ) ) {
					$comment[ array_search( $field_name, $this->aliases, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

			// Create or update comment.
			if ( is_null( $this->id ) ) {
				$id = wp_insert_comment( array_merge( $comment, [ 'comment_type' => hp\prefix( static::_get_meta( 'name' ) ) ] ) );

				if ( $id ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( ! wp_update_comment( array_merge( $comment, [ 'comment_ID' => $this->id ] ) ) ) {
				return false;
			}

			// Update comment meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				update_comment_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
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

		return $id && wp_delete_comment( absint( $id ), true );
	}
}
