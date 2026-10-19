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

		// Filter comment activity.
		add_action( 'load-index.php', [ $this, 'filter_comment_activity' ] );

		// Update comment count.
		add_filter( 'wp_count_comments', [ $this, 'update_comment_count' ], 1000, 2 );

		// Clear comment count.
		add_action( 'wp_insert_comment', [ $this, 'clear_comment_count' ] );
		add_action( 'wp_set_comment_status', [ $this, 'clear_comment_count' ] );

		// Disable notifications.
		add_filter( 'notify_post_author', [ $this, 'disable_notifications' ], 10, 2 );

		// Filter comment list.
		add_filter( 'comments_list_table_query_args', [ $this, 'filter_comment_list' ] );

		// Restrict comment screen.
		add_action( 'load-comment.php', [ $this, 'restrict_comment_screen' ] );

		// Restrict REST comment.
		add_filter( 'rest_prepare_comment', [ $this, 'restrict_rest_comment' ], 1000, 2 );

		// Filter XML-RPC comments.
		add_filter( 'xmlrpc_prepare_comment', [ $this, 'filter_xmlrpc_comment' ], 10, 2 );

		// Restrict comment editing.
		add_filter( 'map_meta_cap', [ $this, 'restrict_comment_editing' ], 10, 4 );
		add_filter( 'rest_request_before_callbacks', [ $this, 'restrict_rest_comment_editing' ], 10, 3 );
		add_action( 'wp_ajax_dim-comment', [ $this, 'restrict_comment_status' ], 0 );

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
			$where .= $wpdb->prepare( " AND comment_type NOT IN ( {$placeholder} )", $types ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- The placeholder list is generated from %s placeholders.
		}

		return $where;
	}

	/**
	 * Filters comment activity.
	 */
	public function filter_comment_activity() {
		add_filter(
			'pre_get_comments',
			function( $query ) {
				$query->query_vars['type__not_in'] = array_merge(
					(array) hp\get_array_value( $query->query_vars, 'type__not_in', [] ),
					hp\prefix( array_keys( hivepress()->get_config( 'comment_types' ) ) )
				);
			}
		);
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

				// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Counts are cached above, there is no API for counting comments by excluded types; the placeholder list is generated from %s placeholders.
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
				// phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare

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

	/**
	 * Filters comment list.
	 *
	 * @param array $args Query arguments.
	 * @return array
	 */
	public function filter_comment_list( $args ) {
		$args['type__not_in'] = array_merge(
			(array) hp\get_array_value( $args, 'type__not_in', [] ),
			$this->get_comment_types()
		);

		return $args;
	}

	/**
	 * Restricts comment screen.
	 */
	public function restrict_comment_screen() {
		$comment = get_comment( absint( hp\get_array_value( $_GET, 'c' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only check, nothing is saved.

		if ( $comment && in_array( $comment->comment_type, $this->get_comment_types(), true ) ) {
			wp_safe_redirect( admin_url( 'edit-comments.php' ) );

			exit;
		}
	}

	/**
	 * Restricts REST comment.
	 *
	 * @param WP_REST_Response $response REST response.
	 * @param WP_Comment       $comment Comment object.
	 * @return WP_REST_Response
	 */
	public function restrict_rest_comment( $response, $comment ) {
		if ( in_array( $comment->comment_type, $this->get_comment_types(), true ) && ! current_user_can( 'manage_options' ) ) {
			return hp\rest_error( 403 );
		}

		return $response;
	}

	/**
	 * Filters XML-RPC comment.
	 *
	 * @param array      $data Comment data.
	 * @param WP_Comment $comment Comment object.
	 * @return array
	 */
	public function filter_xmlrpc_comment( $data, $comment ) {
		if ( in_array( $comment->comment_type, $this->get_comment_types(), true ) && ! current_user_can( 'manage_options' ) ) {
			return [];
		}

		return $data;
	}

	/**
	 * Restricts comment editing.
	 *
	 * @param array  $caps Primitive capabilities.
	 * @param string $cap Meta capability.
	 * @param int    $user_id User ID.
	 * @param array  $args Capability arguments.
	 * @return array
	 */
	public function restrict_comment_editing( $caps, $cap, $user_id, $args ) {
		if ( 'edit_comment' === $cap && isset( $args[0] ) ) {
			$comment = get_comment( $args[0] );

			if ( $comment && strpos( $comment->comment_type, 'hp_' ) === 0 ) {
				$caps[] = in_array( $comment->comment_type, $this->get_comment_types(), true ) ? 'manage_options' : 'moderate_comments';
			}
		}

		return $caps;
	}

	/**
	 * Restricts REST comment editing.
	 *
	 * @param mixed           $response Response.
	 * @param array           $handler Route handler.
	 * @param WP_REST_Request $request REST request.
	 * @return mixed
	 */
	public function restrict_rest_comment_editing( $response, $handler, $request ) {
		if ( 'GET' !== $request->get_method() && preg_match( '#^/wp/v2/comments/(\d+)#', $request->get_route(), $matches ) ) {
			$comment = get_comment( absint( $matches[1] ) );

			if ( $comment && strpos( $comment->comment_type, 'hp_' ) === 0 && ! current_user_can( 'edit_comment', $comment->comment_ID ) ) {
				return new \WP_Error( 'rest_forbidden', '', [ 'status' => 403 ] );
			}
		}

		return $response;
	}

	/**
	 * Restricts comment status.
	 */
	public function restrict_comment_status() {
		$comment = get_comment( absint( hp\get_array_value( $_POST, 'id' ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Read-only check, the nonce is verified by the WordPress handler.

		if ( $comment && strpos( $comment->comment_type, 'hp_' ) === 0 && ! current_user_can( 'edit_comment', $comment->comment_ID ) ) {
			wp_die( -1 );
		}
	}
}
