<?php
/**
 * User controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Menus;
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * User controller class.
 *
 * @class User
 */
final class User extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [

					/**
					 * Users API route.
					 *
					 * @resource Users
					 * @description The users API allows you to register, update and delete users.
					 */
					'users_resource'               => [
						'path' => '/users',
						'rest' => true,
					],

					'user_resource'                => [
						'base' => 'users_resource',
						'path' => '/(?P<user_id>\d+)',
						'rest' => true,
					],

					/**
					 * Registers user.
					 *
					 * @endpoint Register user
					 * @route /users
					 * @method POST
					 * @param string $username Username.
					 * @param string $email Email address.
					 * @param string $password Password.
					 */
					'user_register_action'         => [
						'base'   => 'users_resource',
						'method' => 'POST',
						'action' => [ $this, 'register_user' ],
						'rest'   => true,
					],

					'user_login_action'            => [
						'base'   => 'users_resource',
						'path'   => '/login',
						'method' => 'POST',
						'action' => [ $this, 'login_user' ],
						'rest'   => true,
					],

					'user_password_request_action' => [
						'base'   => 'users_resource',
						'path'   => '/request-password',
						'method' => 'POST',
						'action' => [ $this, 'request_user_password' ],
						'rest'   => true,
					],

					'user_password_reset_action'   => [
						'base'   => 'users_resource',
						'path'   => '/reset-password',
						'method' => 'POST',
						'action' => [ $this, 'reset_user_password' ],
						'rest'   => true,
					],

					/**
					 * Updates user.
					 *
					 * @endpoint Update user
					 * @route /users/<id>
					 * @method POST
					 * @param string $first_name First name.
					 * @param string $last_name Last name.
					 * @param string $description Description.
					 * @param string $email Email address.
					 * @param string $password Password.
					 */
					'user_update_action'           => [
						'base'   => 'user_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_user' ],
						'rest'   => true,
					],

					/**
					 * Deletes user.
					 *
					 * @endpoint Delete user
					 * @route /users/<id>
					 * @method DELETE
					 */
					'user_delete_action'           => [
						'base'   => 'user_resource',
						'method' => 'DELETE',
						'action' => [ $this, 'delete_user' ],
						'rest'   => true,
					],

					'user_account_page'            => [
						'path'     => '/account',
						'redirect' => [ $this, 'redirect_user_account_page' ],
					],

					'user_login_page'              => [
						'title'    => esc_html__( 'Sign In', 'hivepress' ),
						'base'     => 'user_account_page',
						'path'     => '/login',
						'redirect' => [ $this, 'redirect_user_login_page' ],
						'action'   => [ $this, 'render_user_login_page' ],
					],

					'user_password_reset_page'     => [
						'title'    => esc_html__( 'Reset Password', 'hivepress' ),
						'base'     => 'user_account_page',
						'path'     => '/reset-password',
						'redirect' => [ $this, 'redirect_user_password_reset_page' ],
						'action'   => [ $this, 'render_user_password_reset_page' ],
					],

					'user_edit_settings_page'      => [
						'title'    => esc_html__( 'Settings', 'hivepress' ),
						'base'     => 'user_account_page',
						'path'     => '/settings',
						'redirect' => [ $this, 'redirect_user_edit_settings_page' ],
						'action'   => [ $this, 'render_user_edit_settings_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Registers user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function register_user( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() && ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'create_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\User_Register() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Check username.
		if ( $form->get_value( 'username' ) ) {
			if ( sanitize_user( $form->get_value( 'username' ), true ) !== $form->get_value( 'username' ) ) {
				return hp\rest_error( 400, esc_html__( 'Username contains invalid characters.', 'hivepress' ) );
			} elseif ( username_exists( $form->get_value( 'username' ) ) ) {
				return hp\rest_error( 400, esc_html__( 'This username is already in use.', 'hivepress' ) );
			}
		}

		// Check email.
		if ( email_exists( $form->get_value( 'email' ) ) ) {
			return hp\rest_error( 400, esc_html__( 'This email is already registered.', 'hivepress' ) );
		}

		// Get username.
		$username = reset( ( explode( '@', $form->get_value( 'email' ) ) ) );

		if ( $form->get_value( 'username' ) ) {
			$username = $form->get_value( 'username' );
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
		$user = ( new Models\User() )->fill( array_merge( $form->get_values(), [ 'username' => $username ] ) );

		if ( ! $user->save() ) {
			return hp\rest_error( 400, $user->_get_errors() );
		}

		/**
		 * Fires on user registration.
		 *
		 * @action /models/user/register
		 * @description Fires on user registration.
		 * @param int $id User ID.
		 * @param array $values User values.
		 */
		do_action( 'hivepress/v1/models/user/register', $user->get_id(), $form->get_values() );

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_signon(
				[
					'user_login'    => $user->get_username(),
					'user_password' => $form->get_value( 'password' ),
					'remember'      => true,
				]
			);
		}

		return hp\rest_response(
			201,
			[
				'id' => $user->get_id(),
			]
		);
	}

	/**
	 * Logins user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function login_user( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() && ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\User_Login() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get user.
		$user_object = null;

		if ( is_email( $form->get_value( 'username_or_email' ) ) ) {
			$user_object = get_user_by( 'email', $form->get_value( 'username_or_email' ) );
		} else {
			$user_object = get_user_by( 'login', $form->get_value( 'username_or_email' ) );
		}

		if ( empty( $user_object ) ) {
			return hp\rest_error( 401, esc_html__( 'Username or password is incorrect.', 'hivepress' ) );
		}

		$user = Models\User::query()->get_by_id( $user_object );

		// Check password.
		if ( ! wp_check_password( $form->get_value( 'password' ), $user->get_password(), $user->get_id() ) ) {
			return hp\rest_error( 401, esc_html__( 'Username or password is incorrect.', 'hivepress' ) );
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_signon(
				[
					'user_login'    => $user->get_username(),
					'user_password' => $form->get_value( 'password' ),
					'remember'      => true,
				]
			);
		}

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}

	/**
	 * Requests user password.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function request_user_password( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() && ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\User_Password_Request() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get user.
		$user_object = null;

		if ( is_email( $form->get_value( 'username_or_email' ) ) ) {
			$user_object = get_user_by( 'email', $form->get_value( 'username_or_email' ) );
		} else {
			$user_object = get_user_by( 'login', $form->get_value( 'username_or_email' ) );
		}

		if ( empty( $user_object ) ) {
			if ( is_email( $form->get_value( 'username_or_email' ) ) ) {
				return hp\rest_error( 404, esc_html__( 'User with this email doesn\'t exist.', 'hivepress' ) );
			} else {
				return hp\rest_error( 404, esc_html__( 'User with this username doesn\'t exist.', 'hivepress' ) );
			}
		}

		$user = Models\User::query()->get_by_id( $user_object );

		// Send email.
		( new Emails\User_Password_Request(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user_name'          => $user->get_display_name(),
					'password_reset_url' => hivepress()->router->get_url(
						'user_password_reset_page',
						[
							'username'           => $user->get_username(),
							'password_reset_key' => get_password_reset_key( $user_object ),
						]
					),
				],
			]
		) )->send();

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}

	/**
	 * Resets user password.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function reset_user_password( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() && ! wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' ) ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( is_user_logged_in() && ! current_user_can( 'edit_users' ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\User_Password_Reset() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Get user.
		$user_object = check_password_reset_key( $form->get_value( 'password_reset_key' ), $form->get_value( 'username' ) );

		if ( is_wp_error( $user_object ) ) {
			return hp\rest_error( 401, esc_html__( 'Password reset key is expired or invalid.', 'hivepress' ) );
		}

		$user = Models\User::query()->get_by_id( $user_object );

		// Reset password.
		reset_password( $user_object, $form->get_value( 'password' ) );

		// Authenticate user.
		if ( ! is_user_logged_in() ) {
			wp_signon(
				[
					'user_login'    => $user->get_username(),
					'user_password' => $form->get_value( 'password' ),
					'remember'      => true,
				]
			);
		}

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}

	/**
	 * Updates user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function update_user( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get user.
		$user = Models\User::query()->get_by_id( $request->get_param( 'user_id' ) );

		if ( empty( $user ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_users' ) && get_current_user_id() !== $user->get_id() ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\User_Update( [ 'model' => $user ] ) )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		// Check password.
		if ( get_current_user_id() === $user->get_id() && ( $form->get_value( 'email' ) !== $user->get_email() || $form->get_value( 'password' ) ) ) {
			if ( ! $form->get_value( 'current_password' ) ) {
				return hp\rest_error( 400, esc_html__( 'Current password is required.', 'hivepress' ) );
			}

			if ( ! wp_check_password( $form->get_value( 'current_password' ), $user->get_password(), $user->get_id() ) ) {
				return hp\rest_error( 401, esc_html__( 'Current password is incorrect.', 'hivepress' ) );
			}
		}

		// Update user.
		$user->fill( $form->get_values() );

		if ( ! $user->save() ) {
			return hp\rest_error( 400, $user->_get_errors() );
		}

		return hp\rest_response(
			200,
			[
				'id' => $user->get_id(),
			]
		);
	}

	/**
	 * Deletes user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function delete_user( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Get user.
		$user = Models\User::query()->get_by_id( $request->get_param( 'user_id' ) );

		if ( empty( $user ) ) {
			return hp\rest_error( 404 );
		}

		// Check permissions.
		if ( ! current_user_can( 'delete_users' ) && get_current_user_id() !== $user->get_id() ) {
			return hp\rest_error( 403 );
		}

		// Check password.
		if ( get_current_user_id() === $user->get_id() ) {
			$form = ( new Forms\User_Delete() )->set_values( $request->get_params() );

			if ( ! $form->validate() ) {
				return hp\rest_error( 400, $form->get_errors() );
			}

			if ( ! wp_check_password( $form->get_value( 'password' ), $user->get_password(), $user->get_id() ) ) {
				return hp\rest_error( 401, esc_html__( 'Password is incorrect.', 'hivepress' ) );
			}
		}

		// Delete user.
		if ( ! $user->delete() ) {
			return hp\rest_error( 400 );
		}

		return hp\rest_response( 204 );
	}

	/**
	 * Redirects user account page.
	 *
	 * @return mixed
	 */
	public function redirect_user_account_page() {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_url(
				'user_login_page',
				[
					'redirect' => hivepress()->router->get_current_url(),
				]
			);
		}

		// Get menu items.
		$menu_items = ( new Menus\User_Account() )->get_items();

		if ( $menu_items ) {
			return hp\get_array_value( reset( $menu_items ), 'url' );
		}

		return home_url( '/' );
	}

	/**
	 * Redirects user login page.
	 *
	 * @return mixed
	 */
	public function redirect_user_login_page() {
		if ( is_user_logged_in() ) {
			if ( hivepress()->router->get_redirect_url() ) {
				return hivepress()->router->get_redirect_url();
			} else {
				return hivepress()->router->get_url( 'user_account_page' );
			}
		}

		return false;
	}

	/**
	 * Renders user login page.
	 *
	 * @return string
	 */
	public function render_user_login_page() {
		return ( new Blocks\Template( [ 'template' => 'user_login_page' ] ) )->render();
	}

	/**
	 * Redirects user password reset page.
	 *
	 * @return mixed
	 */
	public function redirect_user_password_reset_page() {
		if ( is_user_logged_in() ) {
			if ( hivepress()->router->get_redirect_url() ) {
				return hivepress()->router->get_redirect_url();
			} else {
				return hivepress()->router->get_url( 'user_account_page' );
			}
		}

		return false;
	}

	/**
	 * Renders user password reset page.
	 *
	 * @return string
	 */
	public function render_user_password_reset_page() {
		return ( new Blocks\Template( [ 'template' => 'user_password_reset_page' ] ) )->render();
	}

	/**
	 * Redirects user edit settings page.
	 *
	 * @return mixed
	 */
	public function redirect_user_edit_settings_page() {
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_url(
				'user_login_page',
				[
					'redirect' => hivepress()->router->get_current_url(),
				]
			);
		}

		return false;
	}

	/**
	 * Renders user edit settings page.
	 *
	 * @return string
	 */
	public function render_user_edit_settings_page() {
		return ( new Blocks\Template(
			[
				'template' => 'user_edit_settings_page',

				'context'  => [
					'user' => hivepress()->request->get_user(),
				],
			]
		) )->render();
	}
}
