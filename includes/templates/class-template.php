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
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Template meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name' => hp\get_class_name( static::class ),
			],
			$meta
		);

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters template arguments.
			 *
			 * @filter /templates/{$name}
			 * @description Filters template arguments.
			 * @param string $name Template name.
			 * @param array $args Template arguments.
			 * @param array $meta Template meta.
			 */
			$args = apply_filters( 'hivepress/v1/templates/' . hp\get_class_name( $class ), $args, static::get_meta() );
		}

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps template properties.
	 */
	protected function boot() {}

	/**
	 * Gets template blocks.
	 *
	 * @return array
	 */
	final public function get_blocks() {
		return $this->blocks;
	}
}
