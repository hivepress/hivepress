<?php
/**
 * Comment component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles comments.
 */
final class Comment extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter comment query.
		add_action( 'pre_get_comments', [ $this, 'filter_comment_query' ] );

		// Filter comment feed.
		add_filter( 'comment_feed_where', [ $this, 'filter_comment_feed' ] );

		// Update comment count.
		add_filter( 'wp_count_comments', [ $this, 'update_comment_count' ], 1000, 2 );

		// Clear comment count.
		add_action( 'wp_insert_comment', [ $this, 'clear_comment_count' ] );
		add_action( 'wp_set_comment_status', [ $this, 'clear_comment_count' ] );

		// Disable notifications.
		add_filter( 'notify_post_author', [ $this, 'disable_notifications' ], 10, 2 );

		parent::__construct( $args );
	}

	/**
	 * Gets comment types.
	 *
	 * @return array
	 */
	protected function get_comment_types() {
		return hp\prefix(
			array_keys(
				array_filter(
					hivepress()->get_config( 'comment_types' ),
					function( $args ) {
						return ! hp\get_array_value( $args, 'public', true );
					}
				)
			)
		);
	}

	/**
	 * Filters comment WP query.
	 *
	 * @param WP_Comment_Query $query Comment query.
	 */
	public function filter_comment_query( $query ) {

		// Get comment types.
		$types = $this->get_comment_types();

		if ( empty( $types ) ) {
			return;
		}

		// Check included types.
		$included_type  = hp\get_array_value( $query->query_vars, 'type' );
		$included_types = hp\get_array_value( $query->query_vars, 'type__in' );

		if ( $included_type || $included_types ) {
			return;
		}

		// Get excluded types.
		$excluded_types = array_filter( (array) hp\get_array_value( $query->query_vars, 'type__not_in' ) );

		// Add comment types.
		$excluded_types = array_merge( $excluded_types, $types );

		// Set excluded types.
		$query->query_vars['type__not_in'] = $excluded_types;
	}

	/**
	 * Filters comment RSS feed.
	 *
	 * @param string $where Where clause.
	 * @return string
	 */
	public function filter_comment_feed( $where ) {
		global $wpdb;

		// Get comment types.
		$types = $this->get_comment_types();

		if ( $types ) {

			// Set placeholder.
			$placeholder = implode( ', ', array_fill( 0, count( $types ), '%s' ) );

			// Add clause.
			$where .= $wpdb->prepare( " AND comment_type NOT IN ( {$placeholder} )", $types );
		}

		return $where;
	}

	/**
	 * Updates comment count.
	 *
	 * @param array $stats Comment stats.
	 * @param int   $post_id Post ID.
	 * @return array
	 */
	public function update_comment_count( $stats, $post_id ) {
		global $wpdb;

		if ( ! $post_id ) {

			// Get cached stats.
			$stats = hivepress()->cache->get_cache( 'comment_counts' );

			if ( is_null( $stats ) ) {

				// Set default statuses.
				$statuses = [
					'0'            => 'moderated',
					'1'            => 'approved',
					'spam'         => 'spam',
					'trash'        => 'trash',
					'post-trashed' => 'post-trashed',
				];

				// Set default stats.
				$stats = [
					'total_comments' => 0,
					'all'            => 0,
				];

				foreach ( $statuses as $status ) {
					$stats[ $status ] = 0;
				}

				// Get comment types.
				$types = $this->get_comment_types();

				if ( hp\is_plugin_active( 'woocommerce' ) ) {
					$types = array_merge( $types, [ 'action_log', 'order_note', 'webhook_delivery' ] );
				}

				if ( $types ) {

					// Set placeholder.
					$placeholder = implode( ', ', array_fill( 0, count( $types ), '%s' ) );

					// Get counts.
					$counts = $wpdb->get_results(
						$wpdb->prepare(
							"
							SELECT comment_approved, COUNT(*) AS num_comments
							FROM {$wpdb->comments}
							WHERE comment_type NOT IN ( {$placeholder} )
							GROUP BY comment_approved;
							",
							$types
						),
						ARRAY_A
					);
				} else {
					$counts = $wpdb->get_results(
						"
						SELECT comment_approved, COUNT(*) AS num_comments
						FROM {$wpdb->comments}
						GROUP BY comment_approved;
						",
						ARRAY_A
					);
				}

				// Add stats.
				if ( $counts ) {
					foreach ( $counts as $count ) {
						if ( ! in_array( $count['comment_approved'], [ 'post-trashed', 'trash', 'spam' ], true ) ) {
							$stats['all']            += $count['num_comments'];
							$stats['total_comments'] += $count['num_comments'];
						} elseif ( ! in_array( $count['comment_approved'], [ 'post-trashed', 'trash' ], true ) ) {
							$stats['total_comments'] += $count['num_comments'];
						}

						if ( isset( $statuses[ $count['comment_approved'] ] ) ) {
							$stats[ $statuses[ $count['comment_approved'] ] ] = $count['num_comments'];
						}
					}
				}

				$stats = (object) $stats;

				// Cache stats.
				hivepress()->cache->set_cache( 'comment_counts', null, $stats );
			}
		}

		return $stats;
	}

	/**
	 * Clears comment count.
	 */
	public static function clear_comment_count() {
		hivepress()->cache->delete_cache( 'comment_counts' );
	}

	/**
	 * Disables email notifications.
	 *
	 * @param bool $notify Notify comment author?
	 * @param int  $comment_id Comment ID.
	 * @return bool
	 */
	public function disable_notifications( $notify, $comment_id ) {
		if ( strpos( get_comment_type( $comment_id ), 'hp_' ) === 0 ) {
			return false;
		}

		return $notify;
	}
}
