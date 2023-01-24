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
		add_action( 'hivepress/v1/activate', [ $this, 'unschedule_events' ] );
		add_action( 'hivepress/v1/update', [ $this, 'unschedule_events' ] );

		// Dry run events.
		$this->run_events();

		// Include Action Scheduler.
		require_once hivepress()->get_path() . '/vendor/woocommerce/action-scheduler/action-scheduler.php';

		parent::__construct( $args );
	}

	/**
	 * Schedules an action.
	 *
	 * @param string $hook Hook name.
	 * @param array  $args Hook arguments.
	 * @param int    $time Time to execute.
	 * @param mixed  $interval Recurring interval.
	 * @return mixed
	 */
	public function add_action( $hook, $args = [], $time = null, $interval = null ) {

		// Check if scheduled.
		if ( as_has_scheduled_action( $hook, $args, 'hivepress' ) ) {
			return;
		}

		// Get interval by name.
		if ( is_string( $interval ) ) {
			$interval = hp\get_array_value(
				[
					'hourly'     => HOUR_IN_SECONDS,
					'twicedaily' => DAY_IN_SECONDS / 2,
					'daily'      => DAY_IN_SECONDS,
					'weekly'     => WEEK_IN_SECONDS,
				],
				$interval
			);
		}

		// Schedule an action.
		if ( $interval ) {
			if ( ! $time ) {
				$time = time();
			}

			return as_schedule_recurring_action( $time, $interval, $hook, $args, 'hivepress' );
		} elseif ( $time ) {
			return as_schedule_single_action( $time, $hook, $args, 'hivepress' );
		} else {
			return as_enqueue_async_action( $hook, $args, 'hivepress' );
		}
	}

	/**
	 * Unschedules an action.
	 *
	 * @param string $hook Hook name.
	 * @param array  $args Hook arguments.
	 * @return mixed
	 */
	public function remove_action( $hook, $args = [] ) {
		return as_unschedule_all_actions( $hook, $args, 'hivepress' );
	}

	/**
	 * Schedules events.
	 */
	public function schedule_events() {
		$intervals = [ 'hourly', 'twicedaily', 'daily', 'weekly' ];

		foreach ( $intervals as $interval ) {
			$this->add_action( 'hivepress/v1/events/' . $interval, [], null, $interval );
		}
	}

	/**
	 * Unschedules events.
	 */
	public function unschedule_events() {
		$intervals = [ 'hourly', 'twicedaily', 'daily', 'weekly' ];

		foreach ( $intervals as $interval ) {
			$timestamp = wp_next_scheduled( 'hivepress/v1/events/' . $interval );

			if ( $timestamp ) {
				wp_unschedule_event( $timestamp, 'hivepress/v1/events/' . $interval );
			}
		}
	}

	/**
	 * Dry run events.
	 */
	protected function run_events() {
		$intervals = [ 'hourly', 'twicedaily', 'daily', 'weekly' ];

		foreach ( $intervals as $interval ) {
			add_action( 'hivepress/v1/events/' . $interval, [ $this, 'run_event' ] );
		}
	}

	/**
	 * Dry run event.
	 */
	public function run_event() {
		return;
	}
}
