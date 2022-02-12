<?php
/**
 * Google OAuth button field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Google OAuth button.
 */
class Google_OAuth_Button extends Button {

	/**
	 * OAuth scopes.
	 *
	 * @var array
	 */
	protected $scope = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'display_type' => 'button',
				'caption'      => esc_html__( 'Grant Access', 'hivepress' ),
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		if ( hivepress()->google->is_enabled() ) {
			$attributes = [];

			// Get namespace.
			$namespace = hp\unprefix( $this->name );

			if ( hivepress()->google->get_token( $namespace ) ) {

				// Set caption.
				$this->caption = esc_html__( 'Revoke Access', 'hivepress' );

				// Set URL.
				$attributes['data-url'] = esc_url(
					hivepress()->router->get_url(
						'google_oauth_revoke_access_action',
						[
							'namespace' => $namespace,
							'nonce'     => wp_create_nonce( 'google_oauth_revoke_access' ),
						]
					)
				);
			} else {

				// Get API client.
				$client = hivepress()->google->get_client();

				// Set URL.
				$attributes['data-url'] = esc_url(
					$client->getAuthorizationUrl(
						[
							'prompt' => 'consent',
							'scope'  => (array) $this->scope,
							'state'  => wp_json_encode(
								[
									'namespace' => $namespace,
									'nonce'     => wp_create_nonce( 'google_oauth_grant_access' ),
								]
							),
						]
					)
				);
			}

			// Set component.
			$attributes['data-component'] = 'link';

			// Set attributes.
			$this->attributes = hp\merge_arrays( $this->attributes, $attributes );
		}

		parent::boot();
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( hivepress()->google->is_enabled() ) {

			// Render button.
			$output .= parent::render();
		} else {

			// Render notice.
			$output .= hivepress()->admin->render_notice(
				[
					'type'   => 'error',
					'text'   => esc_html__( 'Please set Google Client ID and Secret to proceed.', 'hivepress' ),
					'inline' => true,
				]
			);
		}

		return $output;
	}
}
