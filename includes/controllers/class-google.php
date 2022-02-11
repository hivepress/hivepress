<?php
/**
 * Google controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages Google APIs.
 */
final class Google extends Controller {

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'routes' => [
					'oauth_base'                        => [
						'path' => '/oauth',
					],

					'google_oauth_base'                 => [
						'base' => 'oauth_base',
						'path' => '/google',
					],

					'google_oauth_grant_access_action'  => [
						'base'     => 'google_oauth_base',
						'path'     => '/grant-access',
						'redirect' => [ $this, 'grant_access' ],
					],

					'google_oauth_revoke_access_action' => [
						'base'     => 'google_oauth_base',
						'path'     => '/revoke-access',
						'redirect' => [ $this, 'revoke_access' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Grants Google OAuth access.
	 *
	 * @return mixed
	 */
	public function grant_access() {

		// Check authorization.
		if ( ! current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check credentials.
		if ( ! hivepress()->google->is_enabled() ) {
			return true;
		}

		// Get state.
		$state = json_decode( wp_unslash( hp\get_array_value( $_GET, 'state' ) ), true );

		if ( ! $state ) {
			return true;
		}

		// Verify nonce.
		$nonce = hp\get_array_value( $state, 'nonce' );

		if ( ! wp_verify_nonce( $nonce, 'google_oauth_grant_access' ) ) {
			return true;
		}

		// Get namespace.
		$namespace = sanitize_key( hp\get_array_value( $state, 'namespace' ) );

		if ( ! $namespace ) {
			return true;
		}

		// Get authorization code.
		$code = hp\get_array_value( $_GET, 'code' );

		if ( ! $code ) {
			return true;
		}

		// Get API client.
		$client = hivepress()->google->get_client();

		try {

			// Get access token.
			$token = $client->getAccessToken(
				'authorization_code',
				[
					'code' => $code,
				]
			);
		} catch ( \Exception $exception ) {
			wp_die( esc_html( $exception->getMessage() ) );
		}

		// Update access token.
		hivepress()->google->update_token( $namespace, $token );

		return admin_url( 'admin.php?page=hp_settings&tab=integrations' );
	}

	/**
	 * Revokes Google OAuth access.
	 *
	 * @return mixed
	 */
	public function revoke_access() {

		// Check authorization.
		if ( ! current_user_can( 'manage_options' ) ) {
			return true;
		}

		// Check credentials.
		if ( ! hivepress()->google->is_enabled() ) {
			return true;
		}

		// Verify nonce.
		$nonce = hp\get_array_value( $_GET, 'nonce' );

		if ( ! wp_verify_nonce( $nonce, 'google_oauth_revoke_access' ) ) {
			return true;
		}

		// Get namespace.
		$namespace = sanitize_key( hp\get_array_value( $_GET, 'namespace' ) );

		if ( ! $namespace ) {
			return true;
		}

		// Delete access token.
		hivepress()->google->delete_token( $namespace );

		return admin_url( 'admin.php?page=hp_settings&tab=integrations' );
	}
}
