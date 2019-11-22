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

		// Update posts.
		add_action( 'save_post', [ $this, 'update_post' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'update_post' ], 10, 3 );

		// Update terms.
		add_action( 'create_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'update_term' ], 10, 3 );

		// Update comments.
		add_action( 'wp_insert_comment', [ $this, 'update_comment' ] );
		add_action( 'edit_comment', [ $this, 'update_comment' ] );
		add_action( 'delete_comment', [ $this, 'update_comment' ] );
	}

	/**
	 * Updates post.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param bool    $update Update flag.
	 */
	public function update_post( $post_id, $post, $update ) {
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
	 * Updates term.
	 *
	 * @param int    $term_id Term ID.
	 * @param int    $term_taxonomy_id Term taxonomy ID.
	 * @param string $taxonomy Taxonomy name.
	 */
	public function update_term( $term_id, $term_taxonomy_id, $taxonomy ) {
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
}
