<?php
/**
 * User component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User component class.
 *
 * @class User
 */
final class User {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Register user.
		add_action( 'hivepress/v1/models/user/register', [ $this, 'register_user' ], 10, 2 );

		// Update user.
		add_action( 'added_user_meta', [ $this, 'update_user' ], 10, 4 );
		add_action( 'updated_user_meta', [ $this, 'update_user' ], 10, 4 );

		// Update image.
		add_action( 'added_post_meta', [ $this, 'update_image' ], 10, 4 );

		// Set image.
		add_filter( 'get_avatar', [ $this, 'set_image' ], 1, 5 );

		// Import users.
		add_action( 'import_start', [ $this, 'import_users' ] );
	}

	// todo.
	public function add_todo_fieds($form) {
		// Set fields.
		$fields = [

		];

		// Add terms checkbox.
		$page_id = hp\get_post_id(
			[
				'post_type'   => 'page',
				'post_status' => 'publish',
				'post__in'    => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
			]
		);

		if ( 0 !== $page_id ) {
			$fields['terms'] = [
				'caption'  => sprintf( hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ), esc_url( get_permalink( $page_id ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'order'    => 1000,
			];
		}
		
		return $form;
	}

	/**
	 * Registers user.
	 *
	 * @param int    $user_id User ID.
	 * @param object $user User instance.
	 */
	public function register_user( $user_id, $user ) {

		// Hide admin bar.
		update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

		// Send emails.
		wp_new_user_notification( $user_id );

		( new Emails\User_Register(
			[
				'recipient' => $user->get_email(),
				'tokens'    => [
					'user_name'     => $user->get_first_name() ? $user->get_first_name() : $user->get_username(),
					'user_password' => $user->get_password(),
				],
			]
		) )->send();
	}

	/**
	 * Updates user.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $user_id User ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_user( $meta_id, $user_id, $meta_key, $meta_value ) {
		if ( 'first_name' === $meta_key ) {

			// Get user.
			$user = get_userdata( $user_id );

			// Update name.
			$name = $user->user_login;

			if ( '' !== $meta_value ) {
				$name = $meta_value;
			}

			wp_update_user(
				[
					'ID'           => $user_id,
					'display_name' => $name,
				]
			);
		}
	}

	/**
	 * Updates image.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $attachment_id Attachment ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_image( $meta_id, $attachment_id, $meta_key, $meta_value ) {
		if ( 'hp_parent_field' === $meta_key && 'image_id' === $meta_value ) {

			// Get attachment.
			$attachment = get_post( $attachment_id );

			if ( 'attachment' === $attachment->post_type && 0 === $attachment->post_parent ) {

				// Update image.
				update_user_meta( absint( $attachment->post_author ), 'hp_image_id', $attachment_id );
			}
		}
	}

	/**
	 * Sets user image.
	 *
	 * @param string $image Image HTML.
	 * @param mixed  $id_or_email User ID.
	 * @param int    $size Image size.
	 * @param string $default Default image.
	 * @param string $alt Image description.
	 * @return string
	 */
	public function set_image( $image, $id_or_email, $size, $default, $alt ) {

		// Get user ID.
		$user_id = 0;

		if ( is_numeric( $id_or_email ) ) {
			$user_id = absint( $id_or_email );
		} elseif ( is_object( $id_or_email ) ) {
			$user_id = absint( $id_or_email->user_id );
		} elseif ( is_email( $id_or_email ) ) {
			$user = get_user_by( 'email', $id_or_email );

			if ( false !== $user ) {
				$user_id = $user->ID;
			}
		}

		if ( 0 !== $user_id ) {

			// Get image URL.
			$image_url = wp_get_attachment_image_src( absint( get_user_meta( $user_id, 'hp_image_id', true ) ), 'thumbnail' );

			if ( false !== $image_url ) {
				$image = '<img src="' . esc_url( reset( $image_url ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '">';
			}
		}

		return $image;
	}

	/**
	 * Imports users.
	 */
	public function import_users() {
		remove_action( 'added_post_meta', [ $this, 'update_image' ] );
	}
}
