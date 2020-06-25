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
final class User extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Register user.
		add_action( 'hivepress/v1/models/user/register', [ $this, 'register_user' ], 10, 2 );

		// Login user.
		add_filter( 'authenticate', [ $this, 'login_user' ], 100 );

		// Update user.
		add_action( 'hivepress/v1/models/user/update', [ $this, 'update_user' ] );

		// Alter registration form.
		add_filter( 'hivepress/v1/forms/user_register', [ $this, 'alter_register_form' ] );

		// Render user image.
		add_filter( 'get_avatar', [ $this, 'render_user_image' ], 1, 5 );

		if ( ! is_admin() ) {

			// Alter templates.
			add_filter( 'hivepress/v1/templates/site_footer_block', [ $this, 'alter_site_footer_block' ] );
		}

		parent::__construct( $args );
	}

	/**
	 * Registers user.
	 *
	 * @param int   $user_id User ID.
	 * @param array $values User values.
	 */
	public function register_user( $user_id, $values ) {

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		// Hide admin bar.
		update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

		// Send emails.
		wp_new_user_notification( $user_id );

		( new Emails\User_Register(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user'          => $user,
					'user_name'     => $user->get_display_name(),
					'user_password' => hp\get_array_value( $values, 'password' ),
				],
			]
		) )->send();
	}

	/**
	 * Logins user.
	 *
	 * @param WP_User $user User object.
	 */
	public function login_user( $user ) {

		// Check email verification.
		if ( get_option( 'hp_user_verify_email' ) && hp\is_class_instance( $user, 'WP_User' ) && $user->hp_email_verify_key ) {
			return new \WP_Error( 'email_not_verified', esc_html__( 'Please check your email to activate your account.', 'hivepress' ) );
		}

		return $user;
	}

	/**
	 * Updates user.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user( $user_id ) {

		// Remove action.
		remove_action( 'hivepress/v1/models/user/update', [ $this, 'update_user' ] );

		// Get user.
		$user = Models\User::query()->get_by_id( $user_id );

		// Get display name.
		$display_name = null;

		switch ( get_option( 'hp_user_display_name' ) ) {
			case 'first_name':
				$display_name = $user->get_first_name();

				break;

			case 'last_name':
				$display_name = $user->get_last_name();

				break;

			case 'full_name':
				$display_name = $user->get_full_name();

				break;
		}

		if ( ! $display_name ) {
			$display_name = $user->get_username();
		}

		// Update display name.
		$user->set_display_name( $display_name )->save_display_name();
	}

	/**
	 * Alters registration form.
	 *
	 * @param array $form Form arguments.
	 * @return array
	 */
	public function alter_register_form( $form ) {

		// Set form message.
		if ( get_option( 'hp_user_verify_email' ) ) {
			$form['redirect'] = false;
			$form['message']  = esc_html__( 'Please check your email to activate your account.', 'hivepress' );
		}

		// Add username field.
		if ( ! get_option( 'hp_user_generate_username' ) ) {
			$form['fields']['username'] = [
				'label'      => esc_html__( 'Username', 'hivepress' ),
				'type'       => 'text',
				'max_length' => 60,
				'required'   => true,
				'_order'     => 5,
			];
		}

		// Get terms page ID.
		$page_id = absint( get_option( 'hp_page_user_registration_terms' ) );

		if ( $page_id ) {

			// Get terms page URL.
			$page_url = get_permalink( $page_id );

			if ( $page_url ) {

				// Add terms field.
				$form['fields']['_terms'] = [
					'caption'   => sprintf( hivepress()->translator->get_string( 'i_agree_to_terms_and_conditions' ), esc_url( $page_url ) ),
					'type'      => 'checkbox',
					'required'  => true,
					'_separate' => true,
					'_order'    => 1000,
				];
			}
		}

		return $form;
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
		$user_object = null;

		if ( is_object( $id_or_email ) ) {
			$user_object = get_userdata( $id_or_email->user_id );
		} elseif ( is_numeric( $id_or_email ) ) {
			$user_object = get_userdata( $id_or_email );
		} elseif ( is_email( $id_or_email ) ) {
			$user_object = get_user_by( 'email', $id_or_email );
		}

		if ( $user_object ) {
			$user = Models\User::query()->get_by_id( $user_object );

			// Render image.
			if ( $user->get_image__url( 'thumbnail' ) ) {
				$image = '<img src="' . esc_url( $user->get_image__url( 'thumbnail' ) ) . '" class="avatar avatar-' . esc_attr( $size ) . ' photo" height="' . esc_attr( $size ) . '" width="' . esc_attr( $size ) . '" alt="' . esc_attr( $alt ) . '">';
			}
		}

		return $image;
	}

	/**
	 * Alters site footer block.
	 *
	 * @param array $template Template arguments.
	 * @return array
	 */
	public function alter_site_footer_block( $template ) {
		if ( ! is_user_logged_in() ) {
			$template = hp\merge_trees(
				$template,
				[
					'blocks' => [
						'modals' => [
							'blocks' => [
								'user_login_modal'    => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Sign In', 'hivepress' ),

									'blocks' => [
										'user_login_form' => [
											'type'   => 'user_login_form',
											'_order' => 10,
										],
									],
								],

								'user_register_modal' => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Register', 'hivepress' ),

									'blocks' => [
										'user_register_form' => [
											'type'   => 'user_register_form',
											'_order' => 10,
										],
									],
								],

								'user_password_request_modal' => [
									'type'   => 'modal',
									'title'  => esc_html__( 'Reset Password', 'hivepress' ),

									'blocks' => [
										'user_password_request_form' => [
											'type'       => 'form',
											'form'       => 'user_password_request',
											'_order'     => 10,

											'attributes' => [
												'class' => [ 'hp-form--narrow' ],
											],
										],
									],
								],
							],
						],
					],
				]
			);
		}

		return $template;
	}
}
