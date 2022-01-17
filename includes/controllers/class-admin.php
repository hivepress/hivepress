<?php
/**
 * Admin controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Models;
use HivePress\Forms;
use HivePress\Blocks;
use HivePress\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Admin controller class.
 *
 * @class Admin
 */
final class Admin extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'admin_base'                 => [
						'path' => '/admin',
					],

					'admin_notices_resource'     => [
						'base' => 'admin_base',
						'path' => '/notices',
						'rest' => true,
					],

					'admin_notice_resource'      => [
						'base' => 'admin_notices_resource',
						'path' => '/(?P<notice_name>[a-z0-9_]+)',
						'rest' => true,
					],

					'admin_notice_update_action' => [
						'base'   => 'admin_notice_resource',
						'method' => 'POST',
						'action' => [ $this, 'update_admin_notice' ],
						'rest'   => true,
					],

					'admin_tools_page' => [
						'url' => [ $this, 'get_admin_tools_url' ],
						'match'    => [ $this, 'is_admin_tools_page' ],
						'redirect' => [ $this, 'redirect_admin_tools_page' ],
						'action'   => [ $this, 'render_admin_tools_page' ],
						'rest' => true,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Updates admin notice.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function update_admin_notice( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return hp\rest_error( 403 );
		}

		// Get notice name.
		$notice_name = substr( sanitize_key( $request->get_param( 'notice_name' ) ), 0, 32 );

		if ( $notice_name && $request->get_param( 'dismissed' ) ) {

			// Get notices.
			$dismissed_notices = array_filter( (array) get_option( 'hp_admin_dismissed_notices' ) );

			// Dismiss notice.
			$dismissed_notices[] = $notice_name;

			update_option( 'hp_admin_dismissed_notices', array_unique( $dismissed_notices ) );
		}

		return hp\rest_response(
			200,
			[
				'name' => $notice_name,
			]
		);
	}

	/**
	 * Matches admin tools page URL.
	 *
	 * @return bool
	 */
	public function is_admin_tools_page() {
		return false;
	}

	/**
	 * Redirects admin tools page.
	 *
	 * @return mixed
	 */
	public function redirect_admin_tools_page() {
		global $pagenow;
		error_log($pagenow);

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hivepress()->router->get_return_url( 'user_login_page' );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return hp\rest_error( 403 );
		}

		return false;
	}

	/**
	 * Renders admin tools page.
	 *
	 * @return string
	 */
	public function render_admin_tools_page() {
		return ( new Blocks\Template(
			[
				'template' => hivepress()->router->get_current_route_name(),
			]
		) )->render();
	}

	/**
	 * Gets listing category view URL.
	 *
	 * @param array $params URL parameters.
	 * @return string
	 */
	public function get_admin_tools_url() {
		return 'Test text';
	}
}
