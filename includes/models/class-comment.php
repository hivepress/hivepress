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
 * Comment.
 */
abstract class Comment extends Model {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'type'  => 'comment',
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

		// Get comment.
		$comment = null;

		if ( is_object( $id ) ) {
			$comment = get_object_vars( $id );
		} else {
			$id = absint( $id );

			$comment = get_comment( $id, ARRAY_A );
		}

		if ( empty( $comment ) || static::_get_meta( 'alias' ) !== $comment['comment_type'] ) {
			return;
		}

		// Get comment meta.
		$meta = array_map(
			function( $values ) {
				return maybe_unserialize( hp\get_first_array_value( $values ) );
			},
			get_comment_meta( $comment['comment_ID'] )
		);

		// Create object.
		$object = ( new static() )->set_id( $comment['comment_ID'] );

		// Get field values.
		$values = [];

		foreach ( $object->_get_fields() as $field_name => $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field->get_arg( '_alias' ) );
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Get comment value.
				$values[ $field_name ] = hp\get_array_value( $comment, $field->get_arg( '_alias' ) );
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

		// Get comment data.
		$comment = [];
		$meta    = [];

		foreach ( $fields as $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Set meta value.
				$meta[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Set comment value.
				$comment[ $field->get_arg( '_alias' ) ] = $field->get_value();
			}
		}

		// Create comment.
		$created = false;

		if ( empty( $this->id ) ) {
			$id = wp_insert_comment( array_merge( $comment, [ 'comment_type' => static::_get_meta( 'alias' ) ] ) );

			if ( $id ) {
				$this->set_id( $id );

				$created = true;
			} else {
				return false;
			}
		}

		// Update comment meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_value, [ null, false ], true ) ) {
				delete_comment_meta( $this->id, $meta_key );
			} else {
				update_comment_meta( $this->id, $meta_key, $meta_value );
			}
		}

		// Update comment.
		if ( ! $created ) {
			if ( empty( $comment ) ) {

				// Fire actions.
				do_action( 'hivepress/v1/models/' . static::_get_meta( 'name' ) . '/update', $this->id, $this );
				do_action( 'hivepress/v1/models/comment/update', $this->id, static::_get_meta( 'alias' ) );
			} else {
				return (bool) wp_update_comment( array_merge( $comment, [ 'comment_ID' => $this->id ] ) );
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

		return $id && wp_delete_comment( absint( $id ), true );
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

		return $id && wp_trash_comment( absint( $id ) );
	}
}
