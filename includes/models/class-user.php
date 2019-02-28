<?php
/**
 * User model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User model class.
 *
 * @class User
 */
class User extends Model {

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'fields'  => [
					// todo.
				],
				'aliases' => [
					'user_login' => 'username',
					'user_email' => 'email',
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	public function save() {

		// Get field values.
		$data = [];
		$meta = [];

		foreach ( $this->get_fields() as $field_name => $field ) {
			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->get_aliases(), true ) ) {
					$data[ array_search( $field_name, $this->get_aliases(), true ) ] = $field->get_value();
				} else {
					$meta[ hp_prefix( $field_name ) ] = $field->get_value();
				}
			} else {
				$this->errors = array_merge( $this->errors, $field->get_errors() );
			}
		}

		// Create or update user.
		if ( empty( $this->errors ) ) {
			if ( $this->get_id() === null ) {
				$this->id = wp_insert_user( $data );

				if ( is_wp_error( $this->get_id() ) ) {
					$this->id = null;

					return false;
				}
			} elseif ( is_wp_error( wp_update_user( array_merge( $data, [ 'ID' => $this->get_id() ] ) ) ) ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_user_meta( $this->get_id(), $meta_key, $meta_value );
			}

			return true;
		}

		return false;
	}

	/**
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	public function delete() {
		return $this->get_id() && wp_delete_user( $this->get_id() );
	}
}
