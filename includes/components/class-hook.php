<?php
/**
 * Hook component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Hook component class.
 *
 * @class Hook
 */
final class Hook extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Update users.
		add_action( 'user_register', [ $this, 'update_user' ] );
		add_action( 'profile_update', [ $this, 'update_user' ] );
		add_action( 'delete_user', [ $this, 'update_user' ] );

		add_action( 'added_user_meta', [ $this, 'update_user_meta' ], 10, 4 );
		add_action( 'updated_user_meta', [ $this, 'update_user_meta' ], 10, 4 );

		// Update posts.
		add_action( 'save_post', [ $this, 'update_post' ], 10, 3 );
		add_action( 'delete_post', [ $this, 'update_post' ] );

		add_action( 'transition_post_status', [ $this, 'update_post_status' ], 10, 3 );

		add_action( 'added_post_meta', [ $this, 'update_post_meta' ], 10, 4 );
		add_action( 'updated_post_meta', [ $this, 'update_post_meta' ], 10, 4 );

		// Update terms.
		add_action( 'create_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'edit_term', [ $this, 'update_term' ], 10, 3 );
		add_action( 'delete_term', [ $this, 'update_term' ], 10, 3 );

		add_action( 'added_term_meta', [ $this, 'update_term_meta' ], 10, 4 );
		add_action( 'updated_term_meta', [ $this, 'update_term_meta' ], 10, 4 );

		// Update comments.
		add_action( 'wp_insert_comment', [ $this, 'update_comment' ] );
		add_action( 'edit_comment', [ $this, 'update_comment' ] );
		add_action( 'delete_comment', [ $this, 'update_comment' ] );

		add_action( 'wp_set_comment_status', [ $this, 'update_comment_status' ], 10, 2 );

		add_action( 'added_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );
		add_action( 'updated_comment_meta', [ $this, 'update_comment_meta' ], 10, 4 );

		// Start import.
		add_action( 'import_start', [ $this, 'start_import' ] );

		parent::__construct( $args );
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
	 * Updates user meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $user_id User ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_user_meta( $meta_id, $user_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update user meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 || in_array( $meta_key, [ 'first_name', 'last_name', 'description' ], true ) ) {
			do_action( 'hivepress/v1/models/user/update_' . hp\unprefix( $meta_key ), $user_id, $meta_value );
		}
	}

	/**
	 * Updates post.
	 *
	 * @param int   $post_id Post ID.
	 * @param mixed $post Post object.
	 * @param bool  $update Update flag.
	 */
	public function update_post( $post_id, $post = null, $update = false ) {

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
			do_action( 'hivepress/v1/models/' . hp\unprefix( $post->post_type ) . '/update_status', $post->ID, $new_status, $old_status );
		}
	}

	/**
	 * Updates post meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $post_id Post ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_post_meta( $meta_id, $post_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update post meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$post_type = get_post_type( $post_id );

			if ( strpos( $post_type, 'hp_' ) === 0 ) {
				do_action( 'hivepress/v1/models/' . hp\unprefix( $post_type ) . '/update_' . hp\unprefix( $meta_key ), $post_id, $meta_value );
			}
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
	 * Updates term meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $term_id Term ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_term_meta( $meta_id, $term_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update term meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$taxonomy = get_term( $term_id )->taxonomy;

			if ( strpos( $taxonomy, 'hp_' ) === 0 ) {
				do_action( 'hivepress/v1/models/' . hp\unprefix( $taxonomy ) . '/update_' . hp\unprefix( $meta_key ), $term_id, $meta_value );
			}
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
		if ( 'delete' !== $new_status ) {
			$comment_type = get_comment_type( $comment_id );

			if ( strpos( $comment_type, 'hp_' ) === 0 ) {
				do_action( 'hivepress/v1/models/' . hp\unprefix( $comment_type ) . '/update_status', $comment_id, $new_status );
			}
		}
	}

	/**
	 * Updates comment meta.
	 *
	 * @param int    $meta_id Meta ID.
	 * @param int    $comment_id Comment ID.
	 * @param string $meta_key Meta key.
	 * @param string $meta_value Meta value.
	 */
	public function update_comment_meta( $meta_id, $comment_id, $meta_key, $meta_value ) {

		// Check import status.
		if ( $this->is_import_started() ) {
			return;
		}

		// Update comment meta.
		if ( strpos( $meta_key, 'hp_' ) === 0 ) {
			$comment_type = get_comment_type( $comment_id );

			if ( strpos( $comment_type, 'hp_' ) === 0 ) {
				do_action( 'hivepress/v1/models/' . hp\unprefix( $comment_type ) . '/update_' . hp\unprefix( $meta_key ), $comment_id, $meta_value );
			}
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
	protected function is_import_started() {
		return defined( 'HP_IMPORT' ) && HP_IMPORT;
	}
}
