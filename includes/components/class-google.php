<?php
/**
 * Google component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use League\OAuth2\Client\Provider;
use League\OAuth2\Client\Grant;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements integration with Google APIs.
 */
final class Google extends Component {

	/**
	 * Checks API credentials.
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return get_option( 'hp_google_client_id' ) && get_option( 'hp_google_client_secret' );
	}

	/**
	 * Gets API client.
	 *
	 * @return object
	 */
	public function get_client() {
		return new Provider\Google(
			[
				'accessType'   => 'offline',
				'clientId'     => get_option( 'hp_google_client_id' ),
				'clientSecret' => get_option( 'hp_google_client_secret' ),
				'redirectUri'  => hivepress()->router->get_url( 'google_oauth_grant_access_action' ),
			]
		);
	}

	/**
	 * Gets access token.
	 *
	 * @param string $namespace Namespace.
	 * @return mixed
	 */
	public function get_token( $namespace ) {

		// Get namespace prefix.
		$prefix = hp\prefix( $namespace );

		// Get access token.
		$access_token = get_option( $prefix . '_access_token', null );

		if ( $access_token ) {

			// Get token expiration.
			$expiration = absint( get_option( $prefix . '_token_expiration' ) );

			if ( $expiration <= time() ) {

				// Get refresh token.
				$refresh_token = get_option( $prefix . '_refresh_token' );

				// Get API client.
				$client = $this->get_client();

				try {

					// Get access token.
					$token = $client->getAccessToken(
						( new Grant\RefreshToken() ),
						[
							'refresh_token' => $refresh_token,
						]
					);
				} catch ( \Exception $exception ) {

					// Delete access token.
					$this->delete_token( $namespace );

					return;
				}

				// Update access token.
				$this->update_token( $namespace, $token );

				// Set access token.
				$access_token = $token->getToken();
			}
		}

		return $access_token;
	}

	/**
	 * Updates access token.
	 *
	 * @param string $namespace Namespace.
	 * @param object $token Token object.
	 */
	public function update_token( $namespace, $token ) {

		// Get namespace prefix.
		$prefix = hp\prefix( $namespace );

		// Set access token.
		update_option( $prefix . '_access_token', $token->getToken() );

		// Set token expiration.
		update_option( $prefix . '_token_expiration', $token->getExpires() );

		// Set refresh token.
		if ( $token->getRefreshToken() ) {
			update_option( $prefix . '_refresh_token', $token->getRefreshToken() );
		}
	}

	/**
	 * Deletes access token.
	 *
	 * @param string $namespace Namespace.
	 */
	public function delete_token( $namespace ) {

		// Get namespace prefix.
		$prefix = hp\prefix( $namespace );

		// Delete access token.
		delete_option( $prefix . '_access_token' );
		delete_option( $prefix . '_token_expiration' );
		delete_option( $prefix . '_refresh_token' );
	}
}
