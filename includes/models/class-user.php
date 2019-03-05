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
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
		$args = merge_arrays(
			$args,
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
						'label'    => esc_html__( 'Password', 'hivepress' ),
						'type'     => 'password',
						'required' => true,
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

					// todo move to form.
					'image_id'    => [
						'label'        => esc_html__( 'Profile Image', 'hivepress' ),
						'caption'      => esc_html__( 'Select Image', 'hivepress' ),
						'type'         => 'attachment_upload',
						'file_formats' => [ 'jpg', 'jpeg', 'png' ],
					],
				],

				'aliases' => [
					'user_login' => 'username',
					'user_email' => 'email',
					'user_pass'  => 'password',
				],
			]
		);

		parent::init( $args );
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance values.
		$data = [];
		$meta = [];

		foreach ( self::$fields as $field_name => $field ) {
			$field->set_value( get_array_value( $this->values, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, self::$aliases, true ) ) {
					$data[ array_search( $field_name, self::$aliases, true ) ] = $field->get_value();
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
				$id = wp_insert_user( $data );

				if ( ! is_wp_error( $id ) ) {
					$this->set_id( $id );
				} else {
					return false;
				}
			} elseif ( is_wp_error( wp_update_user( array_merge( $data, [ 'ID' => $this->id ] ) ) ) ) {
				return false;
			}

			foreach ( $meta as $meta_key => $meta_value ) {
				update_user_meta( $this->id, prefix( $meta_key ), $meta_value );
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
	final public function delete() {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		return $this->id && wp_delete_user( $this->id );
	}
}
