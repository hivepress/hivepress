<?php
/**
 * User model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User model class.
 *
 * @class User
 */
class User extends Model {

	/**
	 * Model meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields'  => [
					'username'    => [
						'label'      => esc_html__( 'Username', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 60,
						'required'   => true,
					],

					'email'       => [
						'label'    => esc_html__( 'Email', 'hivepress' ),
						'type'     => 'email',
						'required' => true,
					],

					'password'    => [
						'label'      => esc_html__( 'Password', 'hivepress' ),
						'type'       => 'password',
						'min_length' => 8,
					],

					'first_name'  => [
						'label'      => esc_html__( 'First Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
					],

					'last_name'   => [
						'label'      => esc_html__( 'Last Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
					],

					'description' => [
						'label'      => esc_html__( 'Profile Info', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
					],

					'image_id'    => [
						'label'   => esc_html__( 'Profile Image', 'hivepress' ),
						'caption' => esc_html__( 'Select Image', 'hivepress' ),
						'type'    => 'attachment_upload',
						'formats' => [ 'jpg', 'jpeg', 'png' ],
					],
				],

				'aliases' => [
					'user_login' => 'username',
					'user_email' => 'email',
					'user_pass'  => 'password',
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets object.
	 *
	 * @param int $id Object ID.
	 * @return mixed
	 */
	final public function get( $id ) {

		// Get user.
		$user = null;

		if ( is_object( $id ) ) {
			$user = get_object_vars( $id->data );
		} else {
			$data = get_userdata( absint( $id ) );

			if ( $data ) {
				$user = get_object_vars( $data->data );
			}
		}

		if ( is_null( $user ) ) {
			return;
		}

		// Get user meta.
		$meta = array_map(
			function( $values ) {
				return reset( $values );
			},
			get_user_meta( $user['ID'] )
		);

		// Get object attributes.
		$attributes = [];

		foreach ( array_keys( $this->fields ) as $field_name ) {
			if ( in_array( $field_name, $this->aliases, true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $user, array_search( $field_name, $this->aliases, true ) );
			} elseif ( in_array( $field_name, [ 'first_name', 'last_name', 'description' ], true ) ) {
				$attributes[ $field_name ] = hp\get_array_value( $meta, $field_name );
			} else {
				$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
			}
		}

		return ( new static() )->set_id( $user['ID'] )->fill( $attributes );
	}

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	final public function save() {

		// Get user data.
		$user = [];
		$meta = [];

		foreach ( $this->fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, $this->aliases, true ) ) {
					$user[ array_search( $field_name, $this->aliases, true ) ] = $field->get_value();
				} else {
					$meta[ $field_name ] = $field->get_value();
				}
			} else {
				$this->_add_errors( $field->get_errors() );
			}
		}

		if ( empty( $this->errors ) ) {

			// Create or update user.
			if ( is_null( $this->id ) ) {
				$id = wp_insert_user( $user );

				if ( ! is_wp_error( $id ) ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( is_wp_error( wp_update_user( array_merge( $user, [ 'ID' => $this->id ] ) ) ) ) {
				return false;
			}

			// Update user meta.
			foreach ( $meta as $meta_key => $meta_value ) {
				if ( in_array( $meta_key, [ 'first_name', 'last_name', 'description' ], true ) ) {
					update_user_meta( $this->id, $meta_key, $meta_value );
				} else {
					update_user_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
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
		require_once ABSPATH . 'wp-admin/includes/user.php';

		if ( is_null( $id ) ) {
			$id = $this->id;
		}

		return $id && wp_delete_user( absint( $id ) );
	}
}
