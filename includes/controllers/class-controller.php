<?php
/**
 * Abstract controller.
 *
 * @package HivePress\Controllers
 */

namespace HivePress\Controllers;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract controller class.
 *
 * @OA\Info(title="HivePress REST API", version="1.0", description="This is a reference of all the endpoints available in HivePress REST API.")
 * @OA\Server(url="/wp-json/hivepress/v1", description="")
 */
abstract class Controller {
	use Traits\Mutator;

	/**
	 * Controller routes.
	 *
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Controller arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps controller properties.
	 */
	protected function boot() {}

	/**
	 * Gets controller routes.
	 *
	 * @return array
	 */
	final public function get_routes() {
		return $this->routes;
	}
}
