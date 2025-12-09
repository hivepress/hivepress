<?php
/**
 * Request component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Traits;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Handles the request context.
 */
final class Request extends Component {
	use Traits\Context;

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set request context.
		add_action( 'init', [ $this, 'set_request_context' ], 100 );

		parent::__construct( $args );
	}

	/**
	 * Gets query parameters.
	 *
	 * @return array
	 */
	public function get_params() {
		global $wp_query;

		// Filter parameters.
		$params = array_filter(
			$wp_query->query_vars,
			function ( $param ) {
				return strpos( $param, 'hp_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		// Set parameters.
		$params = array_combine( hp\unprefix( array_keys( $params ) ), $params );

		unset( $params['route'] );

		return $params;
	}

	/**
	 * Gets query parameter.
	 *
	 * @param string $name Parameter name.
	 * @return mixed
	 */
	public function get_param( $name ) {
		return get_query_var( hp\prefix( $name ) );
	}

	/**
	 * Sets object context value.
	 *
	 * @param string $name Context name.
	 * @param mixed  $value Context value.
	 */
	public function set_context( $name, $value ) {
		$this->context[ $name ] = $value;
	}

	/**
	 * Gets the current user.
	 *
	 * @return object
	 */
	public function get_user() {
		return $this->get_context( 'user' );
	}

	/**
	 * Sets the current post.
	 *
	 * @param WP_Post $current_post Post object.
	 * @param bool    $force Force override?
	 */
	public function set_post( $current_post, $force = true ) {
		global $post;

		if ( $post && ! $force ) {
			return;
		}

		$this->set_context( 'post', $post );

		$post = $current_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		setup_postdata( $post );
	}

	/**
	 * Resets the default post.
	 */
	public function reset_post() {
		global $post;

		$default_post = $this->get_context( 'post' );

		if ( ! $default_post ) {
			return;
		}

		$post = $default_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited

		wp_reset_postdata();

		$this->set_context( 'post', null );
	}

	/**
	 * Gets the current page number.
	 *
	 * @return int
	 */
	public function get_page_number() {
		if ( ! $this->get_context( 'page_number' ) ) {
			$page_number = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$page_number = get_query_var( 'page' ) ? get_query_var( 'page' ) : $page_number;

			$this->set_context( 'page_number', absint( $page_number ) );
		}

		return $this->get_context( 'page_number' );
	}

	/**
	 * Sets the current request context.
	 */
	public function set_request_context() {

		// Check authentication.
		if ( ! is_user_logged_in() || hp\is_rest() ) {
			return;
		}

		// Set current user.
		$this->set_context( 'user', Models\User::query()->get_by_id( wp_get_current_user() ) );

		/**
		 * Filters the current request context. You can use this hook to store some request-specific values and retrieve them anywhere in the code with the `hivepress()->request->get_context( 'context_key' )` method.
		 *
		 * @hook hivepress/v1/components/request/context
		 * @param {array} $context Context values.
		 * @return {array} Context values.
		 */
		$this->context = apply_filters( 'hivepress/v1/components/request/context', $this->context );
	}
}
