<?php
/**
 * Admin controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

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
					'admin_notices_resource'     => [
						'path' => '/admin-notices',
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
}
