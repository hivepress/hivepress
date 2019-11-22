<?php
/**
 * WordPress component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * WordPress component class.
 *
 * @class WordPress
 */
final class WordPress {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Update users.
		add_action( 'user_register', [ $this, 'update_user' ] );
		add_action( 'profile_update', [ $this, 'update_user' ] );
		add_action( 'delete_user', [ $this, 'update_user' ] );

		// Update posts.
		add_action( 'save_post', [ $this, 'update_post' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'update_post' ] );

		add_action( 'transition_post_status', [ $this, 'update_post_status' ], 10, 3 );

		// Update terms.
		add_action( 'create_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'update_term' ], 10, 3 );

		// Update comments.
		add_action( 'wp_insert_comment', [ $this, 'update_comment' ] );
		add_action( 'edit_comment', [ $this, 'update_comment' ] );
		add_action( 'delete_comment', [ $this, 'update_comment' ] );

		add_action( 'wp_set_comment_status', [ $this, 'update_comment_status' ], 10, 2 );

		// Start import.
		add_action( 'import_start', [ $this, 'start_import' ] );
	}

	/**
	 * Updates user.
	 *
	 * @param int $user_id User ID.
	 */
	public function update_user( $user_id ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update user.
		list($model, $action) = explode( '_', current_action() );

		if ( 'register' === $action ) {
			$action = 'create';
		} elseif ( 'update' !== $action ) {
			$action = 'delete';
		}

		do_action( 'hivepress/v1/models/user/' . $action, $user_id );
	}

	/**
	 * Updates post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param bool    $update Update flag.
	 */
	public function update_post( $post_id, $post, $update ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update post.
		$post_type = get_post_type( $post_id );

		if ( strpos( $post_type, 'hp_' ) === 0 ) {
			list($action, $model) = explode( '_', current_action() );

			if ( 'save' === $action ) {
				if ( $update ) {
					$action = 'update';
				} else {
					$action = 'create';
				}
			}

			do_action( 'hivepress/v1/models/post/' . $action, $post_id );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $post_type ) . '/' . $action, $post_id );
		}
	}

	/**
	 * Updates post status.
	 *
	 * @param string  $new_status New status.
	 * @param string  $old_status Old status.
	 * @param WP_Post $post Post object.
	 */
	public function update_post_status( $new_status, $old_status, $post ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update post status.
		if ( strpos( $post->post_type, 'hp_' ) === 0 && $new_status !== $old_status ) {
			do_action( 'hivepress/v1/models/post/update_status', $post_id, $new_status, $old_status );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $post->post_type ) . '/update_status', $post_id, $new_status, $old_status );
		}
	}

	/**
	 * Updates term.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function update_term( $term_id, $term_taxonomy_id, $taxonomy ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update term.
		if ( strpos( $taxonomy, 'hp_' ) === 0 ) {
			list($action, $model) = explode( '_', current_action() );

			if ( 'edit' === $action ) {
				$action = 'update';
			}

			do_action( 'hivepress/v1/models/term/' . $action, $term_id );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $taxonomy ) . '/' . $action, $term_id );
		}
	}

	/**
	 * Updates comment.
	 *
	 * @param int $comment_id Comment ID.
	 */
	public function update_comment( $comment_id ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update comment.
		$comment_type = get_comment_type( $comment_id );

		if ( strpos( $comment_type, 'hp_' ) === 0 ) {
			list($action, $model) = explode( '_', preg_replace( '/^wp_/', '', current_action() ) );

			if ( 'insert' === $action ) {
				$action = 'create';
			} elseif ( 'edit' === $action ) {
				$action = 'update';
			}

			do_action( 'hivepress/v1/models/comment/' . $action, $comment_id );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $comment_type ) . '/' . $action, $comment_id );
		}
	}

	/**
	 * Updates comment status.
	 *
	 * @param int    $comment_id Comment ID.
	 * @param string $new_status New status.
	 */
	public function update_comment_status( $comment_id, $new_status ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update comment status.
		$comment_type = get_comment_type( $comment_id );

		if ( strpos( $comment_type, 'hp_' ) === 0 ) {
			do_action( 'hivepress/v1/models/comment/update_status', $comment_id, $new_status );
			do_action( 'hivepress/v1/models/' . hp\unprefix( $comment_type ) . '/update_status', $comment_id, $new_status );
		}
	}

	/**
	 * Starts import.
	 */
	public function start_import() {
		if ( ! defined( 'HP_IMPORT' ) ) {
			define( 'HP_IMPORT', true );
		}
	}

	/**
	 * Checks import status.
	 *
	 * @return bool
	 */
	private function is_import_started() {
		return defined( 'HP_IMPORT' ) && HP_IMPORT;
	}
}
