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

		// Set page context.
		add_action( 'template_redirect', [ $this, 'set_page' ] );

		parent::__construct( $args );
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
	public function get_context( $name = '', $default = null ) {
		$context = $this->context;

		if ( $name ) {
			$context = hp\get_array_value( $context, $name, $default );
		}

		return $context;
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
	 * Sets page context.
	 */
	public function set_page() {

		// Set page title.
		$this->set_context( 'page_title', hp\get_array_value( hivepress()->router->get_current_route(), 'title', get_the_title() ) );

		// Set page number.
		$page_number = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
		$page_number = get_query_var( 'page' ) ? get_query_var( 'page' ) : $page_number;

		$this->set_context( 'page_number', absint( $page_number ) );
	}
}
