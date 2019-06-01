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
	 * Model name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Model fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function init( $args = [] ) {
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
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	final public static function get( $id ) {

		// Get instance data.
		$data = get_userdata( absint( $id ) );

		if ( false !== $data ) {
			$attributes = [];

			// Convert instance data.
			$data = (array) $data->data;

			// Get instance meta.
			$meta = array_map(
				function( $meta_values ) {
					return reset( $meta_values );
				},
				get_user_meta( $data['ID'] )
			);

			// Get instance attributes.
			foreach ( array_keys( static::$fields ) as $field_name ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$attributes[ $field_name ] = hp\get_array_value( $data, array_search( $field_name, static::$aliases, true ) );
				} elseif ( in_array( $field_name, [ 'first_name', 'last_name', 'description' ], true ) ) {
					$attributes[ $field_name ] = hp\get_array_value( $meta, $field_name );
				} else {
					$attributes[ $field_name ] = hp\get_array_value( $meta, hp\prefix( $field_name ) );
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
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	final public function save() {

		// Alias instance attributes.
		$data = [];
		$meta = [];

		foreach ( static::$fields as $field_name => $field ) {
			$field->set_value( hp\get_array_value( $this->attributes, $field_name ) );

			if ( $field->validate() ) {
				if ( in_array( $field_name, static::$aliases, true ) ) {
					$data[ array_search( $field_name, static::$aliases, true ) ] = $field->get_value();
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
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	final public function delete() {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		return $this->id && wp_delete_user( $this->id );
	}

	/**
	 * Gets image ID.
	 *
	 * @return mixed
	 */
	final public function get_image_id() {
		$image_id = hp\get_post_id(
			[
				'post_type'   => 'attachment',
				'post_parent' => 0,
				'author'      => $this->id,
				'meta_key'    => 'hp_parent_field',
				'meta_value'  => 'image_id',
			]
		);

		return 0 !== $image_id ? $image_id : null;
	}
}
