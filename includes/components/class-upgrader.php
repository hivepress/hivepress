<?php
/**
 * Upgrader component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Upgrader component class.
 *
 * @class Upgrader
 */
final class Upgrader extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Upgrade database.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_database' ] );

		// Upgrade events.
		add_action( 'hivepress/v1/update', [ $this, 'upgrade_events' ] );

		parent::__construct( $args );
	}

	/**
	 * Upgrades database.
	 *
	 * @param string $version Old version.
	 */
	public function upgrade_database( $version ) {
		global $wpdb;

		if ( empty( $version ) || version_compare( $version, '1.3.0', '<' ) ) {

			// Upgrade user meta.
			$wpdb->update( $wpdb->usermeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );

			// Upgrade post meta.
			$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'hp_submit_limit' ], [ 'meta_key' => 'hp_submission_limit' ] );
			$wpdb->update( $wpdb->postmeta, [ 'meta_key' => 'hp_expire_period' ], [ 'meta_key' => 'hp_expiration_period' ] );

			// Upgrade term meta.
			$wpdb->update( $wpdb->termmeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );

			// Upgrade options.
			$wpdb->update( $wpdb->options, [ 'option_name' => 'hp_email_user_password_request' ], [ 'option_name' => 'hp_email_user_request_password' ] );
		}
	}

	/**
	 * Upgrades events.
	 *
	 * @param string $version Old version.
	 */
	public function upgrade_events( $version ) {
		if ( empty( $version ) || version_compare( $version, '1.3.0', '<' ) ) {

			// Unchedule events.
			$periods = [ 'hourly', 'twicedaily', 'daily' ];

			foreach ( $periods as $period ) {
				$timestamp = wp_next_scheduled( 'hivepress/v1/cron/' . $period );

				if ( $timestamp ) {
					wp_unschedule_event( $timestamp, 'hivepress/v1/cron/' . $period );
				}
			}
		}
	}
}
