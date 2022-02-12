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
 */
abstract class Template {
	use Traits\Mutator;
	use Traits\Meta;
	use Traits\Context;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name' => hp\get_class_name( static::class ),
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters template meta.
			 *
			 * @filter /templates/{$name}/meta
			 * @description Filters template meta.
			 * @param string $name Template name.
			 * @param array $meta Template meta.
			 */
			$meta = apply_filters( 'hivepress/v1/templates/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

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
			 * @param object $object Template object.
			 */
			$args = apply_filters( 'hivepress/v1/templates/' . hp\get_class_name( $class ), $args, $this );
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
	protected function boot() {

		// Filter blocks.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters template blocks.
			 *
			 * @filter /templates/{$name}/blocks
			 * @description Filters template blocks.
			 * @param string $name Template name.
			 * @param array $blocks Template blocks.
			 * @param object $object Template object.
			 */
			$this->blocks = apply_filters( 'hivepress/v1/templates/' . hp\get_class_name( $class ) . '/blocks', $this->blocks, $this );
		}
	}

	/**
	 * Gets template blocks.
	 *
	 * @return array
	 */
	final public function get_blocks() {
		return $this->blocks;
	}

	/**
	 * Sets object context value.
	 *
	 * @param string $name Context name.
	 * @param mixed  $value Context value.
	 */
	final public function set_context( $name, $value = null ) {
		if ( is_array( $name ) ) {
			$this->context = $name;
		} else {
			$this->context[ $name ] = $value;
		}
	}
}
