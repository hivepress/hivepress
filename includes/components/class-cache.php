<?php
/**
 * Cache component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Cache component class.
 *
 * @class Cache
 */
final class Cache {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Schedule events.
		add_action( 'hivepress/v1/activate', [ $this, 'schedule_events' ] );
	}

	/**
	 * Schedules events.
	 */
	public function schedule_events() {
		$recurrences = [ 'hourly', 'daily' ];

		foreach ( $recurrences as $recurrence ) {
			if ( ! wp_next_scheduled( 'hivepress/v1/cron/' . $recurrence ) ) {
				wp_schedule_event( time(), $recurrence, 'hivepress/v1/cron/' . $recurrence );
			}
		}
	}

	/**
	 * Gets cache.
	 *
	 * @param mixed $name Cache name.
	 * @return mixed
	 */
	public function get_cache( $name ) {
		return get_transient( $this->get_cache_name( $name ) );
	}

	/**
	 * Sets cache.
	 *
	 * @param mixed $name Cache name.
	 * @param mixed $value Cache value.
	 * @param int   $expiration Expiration period.
	 */
	public function set_cache( $name, $value, $expiration = 0 ) {
		set_transient( $this->get_cache_name( $name ), $value, $expiration );
	}

	/**
	 * Clears cache.
	 *
	 * @param mixed $name Cache name.
	 */
	public function clear_cache( $name ) {
		global $wpdb;

		if ( strpos( $name, '*' ) !== false ) {

			// Get transients.
			$transients = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s",
					'_transient_' . hp\prefix( str_replace( '*', '%', $name ) )
				)
			);

			// Delete transients.
			foreach ( $transients as $transient ) {
				delete_transient( substr( $transient, strlen( '_transient_' ) ) );
			}
		} else {
			delete_transient( hp\prefix( $name ) );
		}
	}

	/**
	 * Gets cache name.
	 *
	 * @param mixed $names Cache names.
	 * @return string
	 */
	protected function get_cache_name( $names ) {
		$name = $names;

		if ( is_array( $names ) ) {
			$name = '';

			ksort( $names );

			foreach ( $names as $part ) {
				if ( is_array( $part ) ) {
					$part = md5( wp_json_encode( $part ) );
				}

				$name .= '_' . strval( $part );
			}

			$name = ltrim( $name, '_' );
		}

		return hp\prefix( $name );
	}
}
