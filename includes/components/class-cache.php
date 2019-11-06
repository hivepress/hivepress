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

		// Clear cache.
		add_action( 'save_post', [ $this, 'clear_post_cache' ] );
		add_action( 'delete_post', [ $this, 'clear_post_cache' ] );

		add_action( 'create_term', [ $this, 'clear_term_cache' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'clear_term_cache' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'clear_term_cache' ], 10, 3 );
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
	 * Clears post cache.
	 *
	 * @param int $post_id Post ID.
	 */
	public function clear_post_cache( $post_id ) {
		$post_type = get_post_type( $post_id );

		if ( in_array( $post_type, hp\prefix( array_keys( hivepress()->get_config( 'post_types' ) ) ), true ) ) {
			$this->clear_cache( hp\unprefix( $post_type ) . '/*' );
		}
	}

	/**
	 * Clears term cache.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function clear_term_cache( $term_id, $term_taxonomy_id, $taxonomy ) {
		if ( in_array( $taxonomy, hp\prefix( array_keys( hivepress()->get_config( 'taxonomies' ) ) ), true ) ) {
			$this->clear_cache( hp\unprefix( $taxonomy ) . '/*' );
		}
	}

	/**
	 * Gets cache.
	 *
	 * @param mixed $names Cache names.
	 * @return mixed
	 */
	public function get_cache( $names ) {
		$cache = null;

		// Get meta value.
		$id = null;

		if ( is_array( $names ) && count( $names ) > 2 ) {
			$id = reset( $names );

			if ( is_numeric( $id ) ) {
				array_shift( $names );

				$callback = 'get_' . array_shift( $names ) . '_meta';

				if ( function_exists( $callback ) ) {
					$cache = call_user_func_array( $callback, [ $id, $this->get_cache_name( $names ), true ] );
				}
			}
		}

		// Get transient value.
		if ( ! is_numeric( $id ) ) {
			$cache = get_transient( $this->get_cache_name( $names ) );
		}

		// Normalize value.
		if ( in_array( $cache, [ false, '' ], true ) ) {
			$cache = null;
		}

		return $cache;
	}

	/**
	 * Sets cache.
	 *
	 * @param mixed $names Cache names.
	 * @param mixed $value Cache value.
	 * @param int   $expiration Expiration period.
	 */
	public function set_cache( $names, $value, $expiration = 0 ) {

		// Set meta value.
		$id = null;

		if ( is_array( $names ) && count( $names ) > 2 ) {
			$id = reset( $names );

			if ( is_numeric( $id ) ) {
				array_shift( $names );

				$callback = 'update_' . array_shift( $names ) . '_meta';

				if ( function_exists( $callback ) ) {
					$cache = call_user_func_array( $callback, [ $id, $this->get_cache_name( $names ), $value ] );
				}
			}
		}

		// Set transient value.
		if ( ! is_numeric( $id ) ) {
			set_transient( $this->get_cache_name( $names ), $value, $expiration );
		}
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
					'_transient_' . hp\prefix( str_replace( '*', '%', str_replace( '_', '\_', $name ) ) )
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

				$name .= '/' . strval( $part );
			}

			$name = ltrim( $name, '/' );
		}

		return hp\prefix( $name );
	}
}
