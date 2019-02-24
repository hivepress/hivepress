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
	 */
	public function __construct() {
		parent::__construct();

		// Set routes.
		$this->set_routes(
			[
				[
					'path'      => '/users',
					'rest'      => true,
					'endpoints' => [
						[
							'path'    => '/(?P<id>\d+)',
							'methods' => 'GET',
							'action'  => 'get_user',
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
			]
		);
	}

	/**
	 * Gets user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function get_user( $request ) {
		// todo.
	}

	/**
	 * Updates user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function update_user( $request ) {
		// todo.
	}

	/**
	 * Deletes user.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function delete_user( $request ) {
		// todo.
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
