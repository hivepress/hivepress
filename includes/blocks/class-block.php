<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {
	use Traits\Mutator;

	use Traits \Meta {
		set_meta as _set_meta;
	}

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Block context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Block meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name'     => hp\get_class_name( static::class ),
				'settings' => [],
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters block meta.
			 *
			 * @filter /blocks/{$type}/meta
			 * @description Filters block meta.
			 * @param string $type Block type.
			 * @param array $meta Block meta.
			 */
			$meta = apply_filters( 'hivepress/v1/blocks/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters block arguments.
			 *
			 * @filter /blocks/{$type}
			 * @description Filters block arguments.
			 * @param string $type Block type.
			 * @param array $args Block arguments.
			 * @param array $object Block object.
			 */
			$args = apply_filters( 'hivepress/v1/blocks/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {}

	/**
	 * Sets meta values.
	 *
	 * @param array $meta Meta values.
	 */
	final protected static function set_meta( $meta ) {

		// Set settings.
		$settings = array_filter( hp\get_array_value( $meta, 'settings', [] ) );

		if ( $settings ) {
			$meta['settings'] = [];

			foreach ( $settings as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$meta['settings'][ $name ] = $field;
				}
			}
		}

		static::_set_meta( $meta );
	}

	/**
	 * Gets context values.
	 *
	 * @param string $name Context name.
	 * @return mixed
	 */
	final public function get_context( $name = '' ) {
		$context = $this->context;

		if ( $name ) {
			$context = hp\get_array_value( $context, $name );
		}

		return $context;
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
