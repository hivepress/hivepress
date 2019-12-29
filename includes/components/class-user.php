<?php
/**
 * User component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;
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
		add_action( 'hivepress/v1/models/user/register', [ $this, 'register_user' ] );

		// Update user.
		add_action( 'hivepress/v1/models/user/update_first_name', [ $this, 'update_user' ] );

		// Render user image.
		add_filter( 'get_avatar', [ $this, 'render_user_image' ], 1, 5 );

		// Add registration fields.
		add_filter( 'hivepress/v1/forms/user_register', [ $this, 'add_register_fields' ] );
	}

	/**
	 * Registers user.
	 *
	 * @param int $user_id User ID.
	 */
	public function register_user( $user_id ) {

		// Get user.
		$user = Models\User::get_by_id( $user_id );

		// Hide admin bar.
		update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

		// Send emails.
		wp_new_user_notification( $user_id );

		( new Emails\User_Register(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user_name'     => $user->get_display_name(),
					'user_password' => $user->get_password(),
				],
			]
		) )->send();
	}

	/**
	 * Updates user.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user( $user_id ) {

		// Get user.
		$user = Models\User::get_by_id( $user_id );

		// Update user.
		$user->fill(
			[
				'display_name' => $user->get_first_name() ? $user->get_first_name() : $user->get_username(),
			]
		)->save();
	}

	/**
	 * Renders user image.
	 *
	 * @param string $image Image HTML.
	 * @param mixed  $id_or_email User ID.
	 * @param int    $size Image size.
	 * @param string $default Default image.
	 * @param string $alt Image description.
	 * @return string
	 */
	public function render_user_image( $image, $id_or_email, $size, $default, $alt ) {

		// Get user.
		$user = null;

		if ( is_numeric( $id_or_email ) ) {
			$user = Models\User::get_by_id( $id_or_email );
		} elseif ( is_object( $id_or_email ) ) {
			$user = Models\User::get_by_id( $id_or_email->user_id );
		} elseif ( is_email( $id_or_email ) ) {
			$user = Models\User::filter( [ 'email' => $id_or_email ] )->get_first();
		}

		// Render image.
		if ( $user && $user->get_image_url( 'thumbnail' ) ) {
			$image = '<img src="' . esc_url( $user->get_image_url( 'thumbnail' ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '">';
		}

		return $image;
	}

	/**
	 * Adds registration fields.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function add_register_fields( $form ) {

		// Get terms page ID.
		$page_id = reset(
			( get_posts(
				[
					'post_type'      => 'page',
					'post_status'    => 'publish',
					'post__in'       => [ absint( get_option( 'hp_page_user_registration_terms' ) ) ],
					'posts_per_page' => 1,
					'fields'         => 'ids',
				]
			) )
		);

		if ( $page_id ) {

			// Add terms field.
			$form['fields']['registration_terms'] = [
				'caption'  => sprintf( hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ), esc_url( get_permalink( $page_id ) ) ),
				'type'     => 'checkbox',
				'required' => true,
				'_order'   => 1000,
			];
		}

		return $form;
	}
}
