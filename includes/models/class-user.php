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
				update_user_meta( $this->id, hp\prefix( $meta_key ), $meta_value );
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
