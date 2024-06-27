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
 * Manages users.
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
					'users_resource'               => [
						'path'   => '/users',
						'method' => 'GET',
						'action' => [ $this, 'get_users' ],
						'rest'   => true,
					],

					/**
					* @OA\Parameter(
					*     name="user_id",
					*     description="User ID.",
					*     in="path",
					*     required=true,
					*     @OA\Schema(type="integer"),
					* ),
					*/
					'user_resource'                => [
						'base' => 'users_resource',
						'path' => '/(?P<user_id>\d+)',
						'rest' => true,
					],

					/**
					 * @OA\Post(
					 *     path="/users",
					 *     summary="Register a user",
					 *     tags={"Users"},
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="username", type="string", description="Username."),
					 *         @OA\Property(property="email", type="string", description="Email address."),
					 *         @OA\Property(property="password", type="string", description="Password.")
					 *       ),
					 *     ),
					 *     @OA\Response(response="201", description="")
					 * )
					 */
					'user_register_action'         => [
						'base'   => 'users_resource',
						'method' => 'POST',
						'action' => [ $this, 'register_user' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/users/login",
					 *     summary="Login a user",
					 *     tags={"Users"},
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="username_or_email", type="string", description="Username or email address."),
					 *         @OA\Property(property="password", type="string", description="Password.")
					 *       ),
					 *     ),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'user_login_action'            => [
						'base'   => 'users_resource',
						'path'   => '/login',
						'method' => 'POST',
						'action' => [ $this, 'login_user' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/users/request-password",
					 *     summary="Request a password reset",
					 *     tags={"Users"},
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="username_or_email", type="string", description="Username or email address.")
					 *       ),
					 *     ),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'user_password_request_action' => [
						'base'   => 'users_resource',
						'path'   => '/request-password',
						'method' => 'POST',
						'action' => [ $this, 'request_user_password' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/users/reset-password",
					 *     summary="Reset a password",
					 *     tags={"Users"},
					 *     @OA\RequestBody(
					 *       @OA\JsonContent(
					 *         @OA\Property(property="username", type="string", description="Username."),
					 *         @OA\Property(property="password", type="string", description="New password."),
					 *         @OA\Property(property="password_reset_key", type="string", description="Password reset key.")
					 *       ),
					 *     ),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'user_password_reset_action'   => [
						'base'   => 'users_resource',
						'path'   => '/reset-password',
						'method' => 'POST',
						'action' => [ $this, 'reset_user_password' ],
						'rest'   => true,
					],

					/**
					 * @OA\Post(
					 *     path="/users/{user_id}",
					 *     summary="Update a user",
					 *     description="In addition to the default user fields, you can also update custom fields added via the vendor attributes or HivePress extensions.",
					 *     tags={"Users"},
					 *     @OA\Parameter(ref="#/components/parameters/user_id"),
					 *     @OA\RequestBody(@OA\JsonContent(ref="#/components/schemas/User")),
					 *     @OA\Response(response="200", description="")
					 * )
					 */
					'user_update_action'           => [
						'base'   => 'user_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_user' ],
						'rest'   => true,
					],

					/**
					 * @OA\Delete(
					 *     path="/users/{user_id}",
					 *     summary="Delete a user",
					 *     tags={"Users"},
					 *     @OA\Parameter(ref="#/components/parameters/user_id"),
					 *     @OA\Response(response="204", description="")
					 * )
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

					'user_logout_page'             => [
						'base'     => 'user_account_page',
						'path'     => '/logout',
						'redirect' => [ $this, 'redirect_user_logout_page' ],
					],

					'user_password_reset_page'     => [
						'title'    => esc_html__( 'Reset Password', 'hivepress' ),
						'base'     => 'user_account_page',
						'path'     => '/reset-password',
						'redirect' => [ $this, 'redirect_user_password_reset_page' ],
						'action'   => [ $this, 'render_user_password_reset_page' ],
					],

					'user_email_verify_page'       => [
						'title'    => esc_html__( 'Email Verified', 'hivepress' ),
						'base'     => 'user_account_page',
						'path'     => '/verify-email',
						'redirect' => [ $this, 'redirect_user_email_verify_page' ],
						'action'   => [ $this, 'render_user_email_verify_page' ],
					],

					'user_edit_settings_page'      => [
						'title'    => hivepress()->translator->get_string( 'settings' ),
						'base'     => 'user_account_page',
						'path'     => '/settings',
						'redirect' => [ $this, 'redirect_user_edit_settings_page' ],
						'action'   => [ $this, 'render_user_edit_settings_page' ],
					],

					'user_view_page'               => [
						'path'     => '/user/(?P<username>[A-Za-z0-9 _.\-@]+)',
						'title'    => [ $this, 'get_user_view_title' ],
						'redirect' => [ $this, 'redirect_user_view_page' ],
						'action'   => [ $this, 'render_user_view_page' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Gets users.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function get_users( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_others_posts' ) ) {
			return hp\rest_error( 403 );
		}

		// Get search query.
		$query = sanitize_text_field( $request->get_param( 'search' ) );

		if ( strlen( $query ) < 3 ) {
			return hp\rest_error( 400 );
		}

		// Get users.
		$users = Models\User::query()->search( $query )
		->limit( 20 )
		->get();

		// Get results.
		$results = [];

		if ( $request->get_param( 'context' ) === 'list' ) {
			foreach ( $users as $user ) {
				$results[] = [
					'id'   => $user->get_id(),
					'text' => $user->get_username(),
				];
			}
		}

		return hp\rest_response( 200, $results );
	}

	/**
	 * Registers user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function register_user( $request ) {

		// Check permissions.
		if ( ! get_option( 'hp_user_enable_registration', true ) ) {
			return hp\rest_error( 403 );
		}

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
		$username = hp\get_first_array_value( explode( '@', $form->get_value( 'email' ) ) );

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
		$user = new Models\User();

		// @todo remove temporary fix when updated.
		$user->set_id( null );

		$user->fill(
			array_merge(
				$form->get_values(),
				[
					'username' => $username,
				]
			)
		);

		if ( ! $user->save() ) {
			return hp\rest_error( 400, $user->_get_errors() );
		}

		/**
		 * Fires when a new user is registered.
		 *
		 * @hook hivepress/v1/models/user/register
		 * @param {int} $user_id User ID.
		 * @param {array} $values Form values.
		 */
		do_action( 'hivepress/v1/models/user/register', $user->get_id(), $form->get_values() );

		if ( get_option( 'hp_user_verify_email' ) ) {

			// Set email key.
			$email_key = md5( $user->get_email() . time() . wp_rand() );

			update_user_meta( $user->get_id(), 'hp_email_verify_key', $email_key );

			// Set email redirect.
			$email_redirect = wp_validate_redirect( $form->get_value( '_redirect' ) );

			if ( $email_redirect ) {
				update_user_meta( $user->get_id(), 'hp_email_verify_redirect', $email_redirect );
			}

			// Send email.
			( new Emails\User_Email_Verify(
				[
					'recipient' => $user->get_email(),

					'tokens'    => [
						'user'             => $user,
						'user_name'        => $user->get_username(),
						'email_verify_url' => hivepress()->router->get_url(
							'user_email_verify_page',
							[
								'username'         => $user->get_username(),
								'email_verify_key' => $email_key,
							]
						),
					],
				]
			) )->send();
		} elseif ( ! is_user_logged_in() ) {

			// Authenticate user.
			do_action( 'hivepress/v1/models/user/login' );

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

		// Check email key.
		if ( get_option( 'hp_user_verify_email' ) && $user_object->hp_email_verify_key ) {
			return hp\rest_error( 401, esc_html__( 'Please check your email to activate your account.', 'hivepress' ) );
		}

		// Authenticate user.
		if ( ! is_user_logged_in() ) {

			/**
			 * Fires when a user is being authenticated.
			 *
			 * @hook hivepress/v1/models/user/login
			 */
			do_action( 'hivepress/v1/models/user/login' );

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

		// Check email key.
		if ( get_option( 'hp_user_verify_email' ) && $user_object->hp_email_verify_key ) {
			return hp\rest_error( 401, esc_html__( 'Please check your email to activate your account.', 'hivepress' ) );
		}

		// Send email.
		( new Emails\User_Password_Request(
			[
				'recipient' => $user->get_email(),

				'tokens'    => [
					'user'               => $user,
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
			do_action( 'hivepress/v1/models/user/login' );

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
		$form = null;

		if ( $request->get_param( '_form' ) === 'user_update_profile' ) {
			$form = new Forms\User_Update_Profile( [ 'model' => $user ] );
		} else {
			$form = new Forms\User_Update( [ 'model' => $user ] );
		}

		$form->set_values( $request->get_params() );

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

		// Check email.
		if ( get_option( 'hp_user_verify_email' ) && $form->get_value( 'email' ) !== $user->get_email() ) {

			// Set email key.
			$email_key = md5( $form->get_value( 'email' ) . time() . wp_rand() );

			update_user_meta( $user->get_id(), 'hp_email_verify_key', $email_key );
			update_user_meta( $user->get_id(), 'hp_email_verify_address', $form->get_value( 'email' ) );

			// Send email.
			( new Emails\User_Email_Verify(
				[
					'recipient' => $form->get_value( 'email' ),

					'tokens'    => [
						'user'             => $user,
						'user_name'        => $user->get_display_name(),
						'email_verify_url' => hivepress()->router->get_url(
							'user_email_verify_page',
							[
								'username'         => $user->get_username(),
								'email_verify_key' => $email_key,
							]
						),
					],
				]
			) )->send();

			// Set old email.
			$form->set_value( 'email', $user->get_email() );
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

		// Check settings.
		if ( ! get_option( 'hp_user_allow_deletion', true ) ) {
			return hp\rest_error( 403 );
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

			// Clear authentication.
			wp_clear_auth_cookie();
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
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Get menu items.
		$menu_items = ( new Menus\User_Account() )->get_items();

		if ( $menu_items ) {
			return hp\get_array_value( hp\get_first_array_value( $menu_items ), 'url' );
		}

		return true;
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
		return ( new Blocks\Template(
			[
				'template' => 'user_login_page',
			]
		) )->render();
	}

	/**
	 * Redirects user logout page.
	 *
	 * @return mixed
	 */
	public function redirect_user_logout_page() {
		if ( is_user_logged_in() ) {
			wp_logout();
		}

		return true;
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
		return ( new Blocks\Template(
			[
				'template' => 'user_password_reset_page',
			]
		) )->render();
	}

	/**
	 * Redirects user email verify page.
	 *
	 * @return mixed
	 */
	public function redirect_user_email_verify_page() {

		// Check permissions.
		if ( ! get_option( 'hp_user_verify_email' ) ) {
			return true;
		}

		// Get username and email key.
		$username  = sanitize_user( hp\get_array_value( $_GET, 'username' ) );
		$email_key = sanitize_key( hp\get_array_value( $_GET, 'email_verify_key' ) );

		if ( ! $username || ! $email_key ) {
			return true;
		}

		// Get user.
		$user = get_user_by( 'login', $username );

		// Check email key.
		if ( ! $user || $user->hp_email_verify_key !== $email_key ) {
			return true;
		}

		// Delete email key.
		delete_user_meta( $user->ID, 'hp_email_verify_key' );

		if ( is_email( $user->hp_email_verify_address ) ) {

			// Set new email.
			wp_update_user(
				[
					'ID'         => $user->ID,
					'user_email' => $user->hp_email_verify_address,
				]
			);

			// Delete new email.
			delete_user_meta( $user->ID, 'hp_email_verify_address' );

			// Redirect user.
			return hivepress()->router->get_url( 'user_edit_settings_page' );
		}

		// Send email.
		( new Emails\User_Register(
			[
				'recipient' => $user->user_email,

				'tokens'    => [
					'user'          => Models\User::query()->get_by_id( $user->ID ),
					'user_name'     => $user->display_name,
					'user_password' => '********',
				],
			]
		) )->send();

		// Check authentication.
		if ( is_user_logged_in() ) {
			return hivepress()->router->get_url( 'user_account_page' );
		}

		// Authenticate user.
		do_action( 'hivepress/v1/models/user/login' );

		wp_set_auth_cookie( $user->ID, true );

		do_action( 'wp_login', $user->user_login, $user );

		// Redirect user.
		$redirect = $user->hp_email_verify_redirect;

		if ( $redirect ) {
			delete_user_meta( $user->ID, 'hp_email_verify_redirect' );

			return $redirect;
		}

		return false;
	}

	/**
	 * Renders user email verify page.
	 *
	 * @return string
	 */
	public function render_user_email_verify_page() {
		return ( new Blocks\Template(
			[
				'template' => 'user_email_verify_page',
			]
		) )->render();
	}

	/**
	 * Redirects user edit settings page.
	 *
	 * @return mixed
	 */
	public function redirect_user_edit_settings_page() {
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
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

	/**
	 * Gets user view title.
	 *
	 * @return string
	 */
	public function get_user_view_title() {
		$title = null;

		// Get user.
		$user = Models\User::query()->filter(
			[
				'username' => hivepress()->request->get_param( 'username' ),
			]
		)->get_first();

		if ( $user ) {
			$title = sprintf( esc_html__( 'Profile of %s', 'hivepress' ), $user->get_display_name() );
		}

		// Set request context.
		hivepress()->request->set_context( 'viewed_user', $user );

		return $title;
	}

	/**
	 * Redirects user view page.
	 *
	 * @return mixed
	 */
	public function redirect_user_view_page() {

		// Check settings.
		if ( ! get_option( 'hp_user_enable_display' ) ) {
			return true;
		}

		// Get user.
		$user = hivepress()->request->get_context( 'viewed_user' );

		if ( ! $user ) {
			wp_die( esc_html__( 'No users found.', 'hivepress' ) );
		}

		if ( get_option( 'hp_user_verify_email' ) && get_user_meta( $user->get_id(), 'hp_email_verify_key', true ) ) {
			return true;
		}

		// Get vendor ID.
		$vendor_id = Models\Vendor::query()->filter(
			[
				'user'   => $user->get_id(),
				'status' => 'publish',
			]
		)->get_first_id();

		if ( $vendor_id ) {
			return hivepress()->router->get_url( 'vendor_view_page', [ 'vendor_id' => $vendor_id ] );
		}

		return false;
	}

	/**
	 * Renders user view page.
	 *
	 * @return string
	 */
	public function render_user_view_page() {
		return ( new Blocks\Template(
			[
				'template' => 'user_view_page',

				'context'  => [
					'user' => hivepress()->request->get_context( 'viewed_user' ),
				],
			]
		) )->render();
	}
}
