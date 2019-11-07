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

		// Check status.
		if ( defined( 'HP_CACHE' ) && ! HP_CACHE ) {
			return;
		}

		// Delete cache.
		add_action( 'hivepress/v1/cron/daily', [ $this, 'delete_expired_cache' ] );

		add_action( 'save_post', [ $this, 'delete_post_cache' ] );
		add_action( 'delete_post', [ $this, 'delete_post_cache' ] );

		add_action( 'set_object_terms', [ $this, 'delete_post_term_cache' ], 10, 6 );

		add_action( 'create_term', [ $this, 'delete_term_cache' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'delete_term_cache' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'delete_term_cache' ], 10, 3 );

		add_action( 'wp_insert_comment', [ $this, 'delete_comment_cache' ], 10, 2 );
		add_action( 'edit_comment', [ $this, 'delete_comment_cache' ], 10, 2 );
		add_action( 'delete_comment', [ $this, 'delete_comment_cache' ], 10, 2 );
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

		// Check status.
		if ( defined( 'HP_CACHE' ) && ! HP_CACHE ) {
			return;
		}

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
					error_log( $callback . ' _transient_timeout_' . $name );
					$timeout = absint( call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name, true ] ) );
				}

				if ( 0 !== $timeout && $timeout <= time() ) {

					// Delete value.
					$this->delete_cache( [ $id, $type, hp\unprefix( $name ) ] );
				} else {

					// Get value.
					error_log( $callback . ' _transient_' . $name );
					$cache = call_user_func_array( $callback, [ $id, '_transient_' . $name, true ] );
				}
			}
		} else {

			// Get transient value.
			error_log( 'get_transient ' . $name );
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

		// Check status.
		if ( defined( 'HP_CACHE' ) && ! HP_CACHE ) {
			return;
		}

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
				error_log( $callback . ' _transient_' . $name );
				call_user_func_array( $callback, [ $id, '_transient_' . $name, $value ] );

				// Set timeout.
				if ( $timeout > 0 ) {
					error_log( $callback . ' _transient_timeout_' . $name );
					call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name, time() + $timeout ] );
				}
			}
		} else {

			// Set transient value.
			error_log( 'set_transient ' . $name );
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

		// Check status.
		if ( defined( 'HP_CACHE' ) && ! HP_CACHE ) {
			return;
		}

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

					// Delete value.
					error_log( $callback . ' _transient_' . $name );
					call_user_func_array( $callback, [ $id, '_transient_' . $name ] );

					// Delete timeout.
					if ( $expire ) {
						error_log( $callback . ' _transient_timeout_' . $name );
						call_user_func_array( $callback, [ $id, '_transient_timeout_' . $name ] );
					}
				}
			} else {

				// Delete transient.
				error_log( 'delete_transient ' . $name );
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
		error_log( 'Begin clearing cache.' );
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
						error_log( $callback . ' ' . $meta_value['meta_key'] );
						error_log( $callback . ' ' . preg_replace( '/^_transient_timeout/', '_transient', $meta_value['meta_key'] ) );
						call_user_func_array( $callback, [ $meta_value[ $column ], $meta_value['meta_key'] ] );
						call_user_func_array( $callback, [ $meta_value[ $column ], preg_replace( '/^_transient_timeout/', '_transient', $meta_value['meta_key'] ) ] );
					}
				}
			}
		}
		error_log( 'End clearing cache.' );
	}

	/**
	 * Deletes post cache.
	 *
	 * @param int $post_id Post ID.
	 */
	public function delete_post_cache( $post_id ) {
		if ( substr( get_post_type( $post_id ), 0, 3 ) === 'hp_' ) {

			// Get post.
			$post = get_post( $post_id );

			// Delete transients.
			$this->delete_cache( [ hp\unprefix( $post->post_type ), '*' ] );

			// Delete user meta.
			$this->delete_cache( [ $post->post_author, 'user', hp\unprefix( $post->post_type ), '*' ] );
		}
	}

	/**
	 * Deletes post term cache.
	 *
	 * @param int    $post_id Post ID.
	 * @param array  $terms Terms.
	 * @param array  $term_taxonomy_ids Term taxonomy IDs.
	 * @param string $taxonomy Taxonomy name.
	 * @param bool   $append Append property.
	 * @param array  $old_term_taxonomy_ids Old term taxonomy IDs.
	 */
	public function delete_post_term_cache( $post_id, $terms, $term_taxonomy_ids, $taxonomy, $append, $old_term_taxonomy_ids ) {
		if ( substr( $taxonomy, 0, 3 ) === 'hp_' ) {
			$term_taxonomy_ids = array_unique( array_merge( $term_taxonomy_ids, $old_term_taxonomy_ids ) );

			foreach ( $term_taxonomy_ids as $term_taxonomy_id ) {

				// Get term.
				$term = get_term_by( 'term_taxonomy_id', $term_taxonomy_id );

				// Delete cache.
				if ( false !== $term ) {
					$this->delete_cache( [ $term->term_id, 'term', hp\unprefix( get_post_type( $post_id ) ), '*' ] );
				}
			}
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
		if ( substr( $taxonomy, 0, 3 ) === 'hp_' ) {
			$this->delete_cache( [ hp\unprefix( $taxonomy ), '*' ] );
		}
	}

	/**
	 * Deletes comment cache.
	 *
	 * @param int        $comment_id Comment ID.
	 * @param WP_Comment $comment Comment object.
	 */
	public function delete_comment_cache( $comment_id, $comment ) {

		// Get comment.
		if ( is_array( $comment ) ) {
			$comment = get_comment( $commend_id );
		}

		if ( substr( $comment->comment_type, 0, 3 ) === 'hp_' ) {

			// Delete user meta.
			if ( ! empty( $comment->user_id ) ) {
				$this->delete_cache( [ $comment->user_id, 'user', hp\unprefix( $comment->comment_type ), '*' ] );
			}

			// Delete post meta.
			if ( ! empty( $comment->comment_post_ID ) ) {
				$this->delete_cache( [ $comment->comment_post_ID, 'post', hp\unprefix( $comment->comment_type ), '*' ] );
			}
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
		$version = (string) time();

		$this->set_cache( array_merge( (array) $names, [ 'version' ] ), $version );

		return $version;
	}
}
