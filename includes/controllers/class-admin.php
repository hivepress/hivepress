<?php
/**
 * Admin controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages WP admin.
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

					'plugin_deactivate_action'   => [
						'base'   => 'admin_base',
						'path'   => '/deactivate-plugin',
						'method' => 'POST',
						'action' => [ $this, 'deactivate_plugin' ],
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

			// Update options.
			if ( 'hivepress_allow_tracking' === $request->get_param( 'option' ) ) {
				update_option( 'hp_hivepress_allow_tracking', 1 );
			}

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
	 * Deactivates HivePress plugin.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return WP_Rest_Response
	 */
	public function deactivate_plugin( $request ) {

		// Check authentication.
		if ( ! is_user_logged_in() ) {
			return hp\rest_error( 401 );
		}

		// Check permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return hp\rest_error( 403 );
		}

		// Validate form.
		$form = ( new Forms\Plugin_Deactivate() )->set_values( $request->get_params() );

		if ( ! $form->validate() ) {
			return hp\rest_error( 400, $form->get_errors() );
		}

		if ( $form->get_value( 'reason' ) ) {

			// Send feedback.
			wp_remote_post(
				'https://hivepress.io/api/v1/feedback',
				[
					'body' => [
						'action'  => 'deactivate_plugin',
						'reason'  => $form->get_value( 'reason' ),
						'details' => $form->get_value( 'details' ),
					],
				]
			);
		}

		// Deactivate plugin.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		deactivate_plugins( HP_FILE );

		return hp\rest_response( 200, [] );
	}
}
