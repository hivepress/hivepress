<?php
/**
 * Scheduler component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles event scheduling.
 */
final class Scheduler extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Schedule events.
		add_action( 'hivepress/v1/activate', [ $this, 'schedule_events' ] );
		add_action( 'hivepress/v1/update', [ $this, 'schedule_events' ] );

		// Unschedule events.
		add_action( 'hivepress/v1/deactivate', [ $this, 'unschedule_events' ] );

		// Include Action Scheduler.
		require_once hivepress()->get_path() . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

		parent::__construct( $args );
	}

	/**
	 * Add scheduler action.
	 *
	 * @param string $hook Name of the action hook.
	 * @param array  $args Arguments to callback.
	 * @param int    $time Unix timestamp.
	 * @param int    $interval Recurring interval.
	 */
	public function add_action( $hook, $args = [], $time = null, $interval = null ) {

		if ( as_has_scheduled_action( $hook ) ) {
			return false;
		}

		// Set default time.
		$time = time();

		if ( $time ) {
			if ( $interval ) {
				as_schedule_recurring_action( $time, $interval, $hook, $args, 'hivepress' );
			} else {
				as_schedule_single_action( $time, $hook, $args, 'hivepress' );
			}
		} else {
			as_enqueue_async_action( $hook, $args, 'hivepress' );
		}
	}

	/**
	 * Remove scheduler action.
	 *
	 * @param string $hook Name of the action hook.
	 * @param array  $args Arguments to callback.
	 */
	public function remove_action( $hook, $args = [] ) {
		if ( ! as_has_scheduled_action( $hook ) ) {
			return false;
		}

		if ( $args ) {
			as_unschedule_action( $hook, $args, 'hivepress' );
		} else {
			as_unschedule_all_actions( $hook, $args, 'hivepress' );
		}
	}

	/**
	 * Schedules events.
	 */
	public function schedule_events() {
		$periods = [ 'hourly', 'twicedaily', 'daily', 'weekly' ];

		foreach ( $periods as $period ) {
			if ( ! wp_next_scheduled( 'hivepress/v1/events/' . $period ) ) {
				wp_schedule_event( time(), $period, 'hivepress/v1/events/' . $period );
			}
		}
	}

	/**
	 * Unschedules events.
	 */
	public function unschedule_events() {
		$periods = [ 'hourly', 'twicedaily', 'daily', 'weekly' ];

		foreach ( $periods as $period ) {
			$timestamp = wp_next_scheduled( 'hivepress/v1/events/' . $period );

			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'hivepress/v1/events/' . $period );
			}
		}
	}
}
