<?php
/**
 * Request component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Request component class.
 *
 * @class Request
 */
final class Request extends Component {

	/**
	 * Request context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set the current user.
		add_action( 'init', [ $this, 'set_user' ] );

		parent::__construct( $args );
	}

	/**
	 * Gets parameter values.
	 *
	 * @return array
	 */
	public function get_params() {
		global $wp_query;

		// Filter parameters.
		$params = array_filter(
			$wp_query->query_vars,
			function( $param ) {
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
	 * Gets parameter value.
	 *
	 * @param string $name Parameter name.
	 * @return mixed
	 */
	public function get_param( $name ) {
		return get_query_var( hp\prefix( $name ) );
	}

	/**
	 * Sets context value.
	 *
	 * @param string $name Context name.
	 * @param mixed  $value Context value.
	 */
	public function set_context( $name, $value ) {
		$this->context[ $name ] = $value;
	}

	/**
	 * Gets context values.
	 *
	 * @param string $name Context name.
	 * @param mixed  $default Default value.
	 * @return mixed
	 */
	public function get_context( $name = null, $default = null ) {
		return empty( $name ) ? $this->context : hp\get_array_value( $this->context, $name, $default );
	}

	/**
	 * Sets the current user.
	 */
	public function set_user() {
		if ( is_user_logged_in() ) {
			$this->set_context( 'user', Models\User::query()->get_by_id( wp_get_current_user() ) );
		}
	}

	/**
	 * Gets the current user.
	 */
	public function get_user() {
		return $this->get_context( 'user' );
	}

	/**
	 * Gets the current page number.
	 */
	public function get_page_number() {
		if ( ! $this->get_context( 'page_number' ) ) {
			$page_number = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$page_number = get_query_var( 'page' ) ? get_query_var( 'page' ) : $page_number;

			$this->set_context( 'page_number', absint( $page_number ) );
		}

		return $this->get_context( 'page_number' );
	}
}
