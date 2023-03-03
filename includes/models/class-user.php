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
 * User.
 *
 * @OA\Schema(description="")
 */
class User extends Model {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'type' => 'user',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'fields' => [
					'username'        => [
						'label'      => esc_html__( 'Username', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 60,
						'required'   => true,
						'_alias'     => 'user_login',
					],

					'email'           => [
						'label'    => esc_html__( 'Email', 'hivepress' ),
						'type'     => 'email',
						'required' => true,
						'_alias'   => 'user_email',
					],

					'password'        => [
						'label'      => esc_html__( 'Password', 'hivepress' ),
						'type'       => 'password',
						'min_length' => 8,
						'_alias'     => 'user_pass',
					],

					/**
					* @OA\Property(
					*   property="first_name",
					*   type="string",
					*   description="First name.",
					* )
					 */
					'first_name'      => [
						'label'      => esc_html__( 'First Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
						'_alias'     => 'first_name',
						'_external'  => true,
					],

					/**
					* @OA\Property(
					*   property="last_name",
					*   type="string",
					*   description="Last name.",
					* )
					 */
					'last_name'       => [
						'label'      => esc_html__( 'Last Name', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 64,
						'_alias'     => 'last_name',
						'_external'  => true,
					],

					'display_name'    => [
						'type'       => 'text',
						'max_length' => 256,
						'_alias'     => 'display_name',
					],

					/**
					* @OA\Property(
					*   property="description",
					*   type="string",
					*   description="Profile description.",
					* )
					 */
					'description'     => [
						'label'      => esc_html__( 'Profile Info', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'_alias'     => 'description',
						'_external'  => true,
					],

					'registered_date' => [
						'type'   => 'date',
						'format' => 'Y-m-d H:i:s',
						'_alias' => 'user_registered',
					],

					'verified'        => [
						'type'      => 'checkbox',
						'_external' => true,
					],

					'image'           => [
						'label'     => esc_html__( 'Profile Image', 'hivepress' ),
						'caption'   => esc_html__( 'Select Image', 'hivepress' ),
						'type'      => 'attachment_upload',
						'formats'   => [ 'jpg', 'jpeg', 'png' ],
						'_model'    => 'attachment',
						'_external' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets model fields.
	 *
	 * @param string $area Display area.
	 * @return array
	 */
	final public function _get_fields( $area = null ) {
		return array_filter(
			$this->fields,
			function( $field ) use ( $area ) {
				return empty( $area ) || in_array( $area, (array) $field->get_arg( '_display_areas' ), true );
			}
		);
	}

	/**
	 * Gets full name.
	 *
	 * @return string
	 */
	final public function get_full_name() {
		return trim( $this->get_first_name() . ' ' . $this->get_last_name() );
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

		if ( empty( $user ) ) {
			return;
		}

		// Get user meta.
		$meta = array_map(
			function( $values ) {
				return maybe_unserialize( hp\get_first_array_value( $values ) );
			},
			get_user_meta( $user['ID'] )
		);

		// Create object.
		$object = ( new static() )->set_id( $user['ID'] );

		// Get field values.
		$values = [];

		foreach ( $object->_get_fields() as $field_name => $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Get meta value.
				$values[ $field_name ] = hp\get_array_value( $meta, $field->get_arg( '_alias' ) );
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Get user value.
				$values[ $field_name ] = hp\get_array_value( $user, $field->get_arg( '_alias' ) );
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

		// Get user data.
		$user = [];
		$meta = [];

		foreach ( $fields as $field ) {
			if ( $field->get_arg( '_external' ) ) {

				// Set meta value.
				$meta[ $field->get_arg( '_alias' ) ] = $field->get_value();
			} elseif ( ! $field->get_arg( '_relation' ) ) {

				// Set user value.
				$user[ $field->get_arg( '_alias' ) ] = $field->get_value();
			}
		}

		// Create user.
		$created = false;

		if ( empty( $this->id ) ) {
			$id = wp_insert_user( $user );

			if ( ! is_wp_error( $id ) ) {
				$this->set_id( $id );

				$created = true;
			} else {
				return false;
			}
		}

		// Update user meta.
		foreach ( $meta as $meta_key => $meta_value ) {
			if ( in_array( $meta_value, [ null, false ], true ) ) {
				delete_user_meta( $this->id, $meta_key );
			} else {
				update_user_meta( $this->id, $meta_key, $meta_value );
			}
		}

		// Update user.
		if ( ! $created ) {
			if ( empty( $user ) ) {

				// Fire actions.
				do_action( 'hivepress/v2/models/user/update', $this->id, $this );
				do_action( 'hivepress/v1/models/user/update', $this->id, 'user' );
			} else {
				return ! is_wp_error( wp_update_user( array_merge( $user, [ 'ID' => $this->id ] ) ) );
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
		require_once ABSPATH . 'wp-admin/includes/user.php';

		if ( is_null( $id ) ) {
			$id = $this->id;
		}

		return $id && wp_delete_user( absint( $id ) );
	}
}
