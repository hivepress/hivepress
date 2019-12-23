<?php
/**
 * Abstract template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract template class.
 *
 * @class Template
 */
abstract class Template {
	use Traits\Mutator;
	use Traits\Meta;

	/**
	 * Template meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name' => hp\get_class_name( static::class ),
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Bootstraps template properties.
	 */
	protected function bootstrap() {}

	/**
	 * Gets template blocks.
	 *
	 * @return array
	 */
	final public function get_blocks() {
		return $this->blocks;
	}
}
