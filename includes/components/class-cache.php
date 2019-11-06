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

		// Delete cache.
		add_action( 'hivepress/v1/cron/daily', [ $this, 'delete_expired_cache' ] );

		add_action( 'save_post', [ $this, 'delete_post_cache' ] );
		add_action( 'delete_post', [ $this, 'delete_post_cache' ] );

		add_action( 'create_term', [ $this, 'delete_term_cache' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'delete_term_cache' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'delete_term_cache' ], 10, 3 );
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
	 * @param mixed $names Cache names.
	 * @param bool  $expire Expiration check.
	 * @return mixed
	 */
	public function get_cache( $names, $expire = true ) {
		$cache = null;

		// Get cache name.
		$name = $this->get_cache_name( $names );

		// Get cache ID.
		$id = $this->get_cache_id( $names );

		if ( ! empty( $id ) ) {

			// Get meta value.
			$type     = $names[1];
			$callback = 'get_' . $type . '_meta';

			if ( function_exists( $callback ) ) {

				// Get timeout.
				$timeout = 0;

				if ( $expire ) {
					$timeout = absint( call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name, true ] ) );
				}

				if ( 0 !== $timeout && $timeout <= time() ) {

					// Delete value.
					$this->delete_cache( [ $id, $type, hp\unprefix( $name ) ] );
				} else {

					// Get value.
					$cache = call_user_func_array( $callback, [ $id, '_transient_' . $name, true ] );
				}
			}
		} else {

			// Get transient value.
			$cache = get_transient( $name );
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
	 * @param int   $timeout Expiration timeout.
	 */
	public function set_cache( $names, $value, $timeout = 0 ) {

		// Get cache name.
		$name = $this->get_cache_name( $names );

		// Get cache ID.
		$id = $this->get_cache_id( $names );

		if ( ! empty( $id ) ) {

			// Set meta value.
			$type     = $names[1];
			$callback = 'update_' . $type . '_meta';

			if ( function_exists( $callback ) ) {

				// Set value.
				call_user_func_array( $callback, [ $id, '_transient_' . $name, $value ] );

				// Set timeout.
				if ( $timeout > 0 ) {
					call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name, time() + $timeout ] );
				}
			}
		} else {

			// Set transient value.
			set_transient( $name, $value, $timeout );
		}
	}

	/**
	 * Deletes cache.
	 *
	 * @param mixed $names Cache names.
	 * @param bool  $expire Expiration check.
	 */
	public function delete_cache( $names, $expire = true ) {
		if ( is_array( $names ) && end( $names ) === '*' ) {
			array_pop( $names );

			// Update cache version.
			$this->update_cache_version( $names );
		} else {

			// Get cache name.
			$name = $this->get_cache_name( $names );

			// Get cache ID.
			$id = $this->get_cache_id( $names );

			if ( ! empty( $id ) ) {

				// Delete meta value.
				$type     = $names[1];
				$callback = 'delete_' . $type . '_meta';

				if ( function_exists( $callback ) ) {
					call_user_func_array( $callback, [ $id, '_transient_' . $name ] );

					if ( $expire ) {
						call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name ] );
					}
				}
			} else {

				// Delete transient.
				delete_transient( $name );
			}
		}
	}

	/**
	 * Deletes expired cache.
	 */
	public function delete_expired_cache() {
		global $wpdb;

		// Set meta types.
		$types = [ 'user', 'post', 'term', 'comment' ];

		foreach ( $types as $type ) {
			$callback = 'delete_' . $type . '_meta';

			if ( function_exists( $callback ) ) {

				// Get meta values.
				$table  = $wpdb->prefix . $type . 'meta';
				$column = $type . '_id';

				$meta_values = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT {$column}, meta_key FROM {$table} WHERE meta_key LIKE %s AND CAST(meta_value AS SIGNED) <= %d;",
						'\_transient\_timeout\_%',
						time()
					),
					ARRAY_A
				);

				// Delete meta values.
				if ( ! empty( $meta_values ) ) {
					foreach ( $meta_values as $meta_value ) {
						call_user_func_array( $callback, [ $meta_value[ $type . '_id' ], $meta_value[ $meta_key ] ] );
						call_user_func_array( $callback, [ $meta_value[ $type . '_id' ], preg_replace( '/^_transient_timeout/', '_transient', $meta_value[ $meta_key ] ) ] );
					}
				}
			}
		}
	}

	/**
	 * Deletes post cache.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post_cache( $post_id ) {
		if ( in_array( get_post_type( $post_id ), hp\prefix( array_keys( hivepress()->get_config( 'post_types' ) ) ), true ) ) {

			// Get post.
			$post = get_post( $post_id );

			// Delete transients.
			$this->delete_cache( [ hp\unprefix( $post->post_type ), '*' ] );

			// Delete meta.
			$this->delete_cache( [ absint( $post->post_author ), 'user', hp\unprefix( $post->post_type ), '*' ] );
		}
	}

	/**
	 * Deletes term cache.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function delete_term_cache( $term_id, $term_taxonomy_id, $taxonomy ) {
		if ( in_array( $taxonomy, hp\prefix( array_keys( hivepress()->get_config( 'taxonomies' ) ) ), true ) ) {
			$this->delete_cache( [ hp\unprefix( $taxonomy ), '*' ] );
		}
	}

	/**
	 * Gets cache ID.
	 *
	 * @param mixed $names Cache names.
	 * @return int
	 */
	protected function get_cache_id( $names ) {
		$id = null;

		if ( is_array( $names ) && count( $names ) >= 3 ) {
			$id = absint( reset( $names ) );
		}

		return $id;
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

			// Get cache ID.
			$id   = $this->get_cache_id( $names );
			$type = null;

			if ( ! empty( $id ) ) {
				array_shift( $names );

				$type = array_shift( $names );
			}

			// Get cache version.
			$version = '';

			foreach ( $names as $part ) {
				if ( is_array( $part ) ) {
					if ( ! empty( $id ) ) {
						$version = $this->get_cache_version( [ $id, $type, reset( $names ) ] );
					} else {
						$version = $this->get_cache_version( reset( $names ) );
					}

					break;
				}
			}

			// Get cache name.
			$name = array_shift( $names );

			foreach ( $names as $part ) {
				if ( is_array( $part ) ) {
					ksort( $part );

					$part = md5( wp_json_encode( $part ) . $version );
				}

				$name .= '/' . strval( $part );
			}
		}

		return hp\prefix( $name );
	}

	/**
	 * Gets cache version.
	 *
	 * @param mixed $names Cache names.
	 * @return string
	 */
	protected function get_cache_version( $names ) {
		$version = $this->get_cache( array_merge( (array) $names, [ 'version' ] ), false );

		if ( empty( $version ) ) {
			$version = $this->update_cache_version( $names );
		}

		return $version;
	}

	/**
	 * Updates cache version.
	 *
	 * @param mixed $names Cache names.
	 * @return string
	 */
	protected function update_cache_version( $names ) {

		// Get cache names.
		$name = array_merge( (array) $names, [ 'version' ] );

		// Delete old versions.
		$this->delete_cache( $name, false );

		// Set new version.
		$version = (string) time();

		$this->set_cache( $name, $version );

		return $version;
	}
}
