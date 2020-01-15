<?php
/**
 * Updater component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Updater component class.
 *
 * @class Updater
 */
final class Updater extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update meta.
		add_action( 'hivepress/v1/update', [ $this, 'update_meta' ] );

		// Update events.
		add_action( 'hivepress/v1/update', [ $this, 'update_events' ] );

		parent::__construct( $args );
	}

	/**
	 * Updates meta.
	 *
	 * @param string $version Old version.
	 */
	public function update_meta( $version ) {
		global $wpdb;

		if ( empty( $version ) || version_compare( $version, '1.3.0', '<' ) ) {

			// Update user meta.
			$wpdb->update( $wpdb->usermeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );

			// Update term meta.
			$wpdb->update( $wpdb->termmeta, [ 'meta_key' => 'hp_image' ], [ 'meta_key' => 'hp_image_id' ] );
		}
	}

	/**
	 * Updates events.
	 *
	 * @param string $version Old version.
	 */
	public function update_events( $version ) {
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
