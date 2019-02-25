<?php
/**
 * User controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User controller class.
 *
 * @class User
 */
class User extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {

		// Set routes.
		$args['routes'] = [
			[
				'path'      => '/users',
				'rest'      => true,
				'endpoints' => [
					[
						'methods' => 'POST',
						'action'  => 'register_user',
					],

					[
						'path'    => '/(?P<id>\d+)',
						'methods' => 'POST',
						'action'  => 'update_user',
					],

					[
						'path'    => '/(?P<id>\d+)',
						'methods' => 'DELETE',
						'action'  => 'delete_user',
					],
				],
			],

			[
				'title'  => esc_html__( 'Sign In', 'hivepress' ),
				'path'   => '/account/login',
				'action' => 'render_login_page',
			],

			[
				'title'  => esc_html__( 'Reset Password', 'hivepress' ),
				'path'   => '/account/reset-password',
				'action' => 'render_password_page',
			],

			[
				'title'  => esc_html__( 'My Settings', 'hivepress' ),
				'path'   => '/account/settings',
				'action' => 'render_settings_page',
			],
		];

		parent::__construct( $args );
	}

	/**
	 * Registers user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function register_user( $request ) {

		// Validate form.
		$form = new \HivePress\Forms\User_Register();

		if ( ! $form->validate() ) {
			return hp_rest_error( 400, $form->get_errors() );
		}

		// Check username.
		if ( $form->get_value( 'username' ) ) {
			if ( sanitize_user( $form->get_value( 'username' ), true ) !== $form->get_value( 'username' ) ) {
				return hp_rest_error( 400, esc_html__( 'Username contains invalid characters.', 'hivepress' ) );
			} elseif ( username_exists( $form->get_value( 'username' ) ) ) {
				return hp_rest_error( 400, esc_html__( 'This username is already in use.', 'hivepress' ) );
			}
		}

		// Check email.
		if ( email_exists( $this->get_value( 'email' ) ) ) {
			return hp_rest_error( 400, esc_html__( 'This email is already registered.', 'hivepress' ) );
		}

		// Get username.
		list($username, $domain) = explode( '@', $this->get_value( 'email' ) );

		if ( $this->get_value( 'username' ) ) {
			$username = $this->get_value( 'username' );
		} else {
			$username = sanitize_user( $username, true );

			if ( empty( $username ) ) {
				$username = 'user';
			}

			while ( username_exists( $username ) ) {
				$username .= wp_rand( 1, 9 );
			}
		}

		// Register user.
		$user_id = wp_create_user( $username, $this->get_value( 'password' ), $this->get_value( 'email' ) );

		if ( ! is_wp_error( $user_id ) ) {

			// Hide admin bar.
			update_user_meta( $user_id, 'show_admin_bar_front', 'false' );

			// Send emails.
			wp_new_user_notification( $user_id );

			// todo send email.
		}

		return new \WP_Rest_Response( null, 200 );
	}

	/**
	 * Updates user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function update_user( $request ) {

		// Check authorization.
		if ( ! is_user_logged_in() ) {
			return hp_rest_error( 401 );
		}

		// Get user.
		$user = get_userdata( absint( $request->get_param( 'id' ) ) );

		if ( false === $user ) {
			return hp_rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) && get_current_user_id() !== $user->ID ) {
			return hp_rest_error( 403 );
		}

		// Validate form.
		$form = new \HivePress\Forms\User_Update();

		if ( ! $form->validate() ) {
			return hp_rest_error( 400, $form->get_errors() );
		}

		// Update user.
		$first_name   = $form->get_vaue( 'first_name' );
		$last_name    = $form->get_vaue( 'last_name' );
		$display_name = trim( $first_name . ' ' . $last_name );

		update_user_meta( $user->ID, 'first_name', $first_name );
		update_user_meta( $user->ID, 'last_name', $last_name );
		update_user_meta( $user->ID, 'description', $form->get_value( 'description' ) );

		if ( '' !== $display_name ) {
			wp_update_user(
				[
					'ID'           => $user->ID,
					'display_name' => $display_name,
				]
			);
		}

		if ( $form->get_value( 'email' ) !== $user->user_email || $form->get_value( 'new_password' ) ) {

			// Check password.
			if ( ! current_user_can( 'edit_users' ) ) {
				if ( is_null( $form->get_value( 'current_password' ) ) ) {
					return hp_rest_error( 403, esc_html__( 'Current password is required.', 'hivepress' ) );
				}

				if ( ! wp_check_password( $form->get_value( 'current_password' ), $user->user_pass, $user->ID ) ) {
					return hp_rest_error( 403, esc_html__( 'Current password is incorrect.', 'hivepress' ) );
				}
			}

			// Update email.
			if ( $form->get_value( 'email' ) !== $user->user_email ) {
				wp_update_user(
					[
						'ID'         => $user->ID,
						'user_email' => $form->get_value( 'email' ),
					]
				);
			}

			// Change password.
			if ( $form->get_value( 'new_password' ) ) {
				wp_update_user(
					[
						'ID'        => $user->ID,
						'user_pass' => $form->get_value( 'new_password' ),
					]
				);
			}
		}

		return new \WP_Rest_Response( null, 200 );
	}

	/**
	 * Deletes user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function delete_user( $request ) {
		require_once ABSPATH . 'wp-admin/includes/user.php';

		// Check authorization.
		if ( ! is_user_logged_in() ) {
			return hp_rest_error( 401 );
		}

		// Get user.
		$user = get_userdata( absint( $request->get_param( 'id' ) ) );

		if ( false === $user ) {
			return hp_rest_error( 404 );
		}

		// Check permissions.
		if ( ( current_user_can( 'delete_users' ) && get_current_user_id() === $user->ID ) || ( ! current_user_can( 'delete_users' ) && get_current_user_id() !== $user->ID ) ) {
			return hp_rest_error( 403 );
		}

		// Check password.
		if ( ! current_user_can( 'delete_users' ) ) {
			$form = new \HivePress\Forms\User_Delete();

			if ( ! $form->validate() ) {
				return hp_rest_error( 400, $form->get_errors() );
			}

			if ( ! wp_check_password( $form->get_value( 'password' ), $user->user_pass, $user->ID ) ) {
				return hp_rest_error( 403, esc_html__( 'Password is incorrect.', 'hivepress' ) );
			}
		}

		// Delete user.
		wp_delete_user( $user->ID );

		return new \WP_Rest_Response( null, 204 );
	}

	/**
	 * Renders login page.
	 *
	 * @return string
	 */
	public function render_login_page() {
		// todo.
	}

	/**
	 * Renders password page.
	 *
	 * @return string
	 */
	public function render_password_page() {
		// todo.
	}

	/**
	 * Renders settings page.
	 *
	 * @return string
	 */
	public function render_settings_page() {
		// todo.
	}
}
