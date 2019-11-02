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
}
