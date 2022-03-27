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
	 * Template attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

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
			 * Filters the template class meta. The class meta stores properties related to the template type rather than a specific template instance. The dynamic part of the hook refers to the template name (e.g. `listing_view_page`). You can check the available templates in the `includes/templates` directory of HivePress.
			 *
			 * @hook hivepress/v1/templates/{template_name}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
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
			 * Filters the template properties. The dynamic part of the hook refers to the template name (e.g. `listing_view_page`). You can check the available templates in the `includes/templates` directory of HivePress.
			 *
			 * @hook hivepress/v1/templates/{template_name}
			 * @param {array} $props Template properties.
			 * @param {object} $template Template object.
			 * @return {array} Template properties.
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
			 * Filters template blocks. At the time of this hook the template context is already available. The dynamic part of the hook refers to the template name (e.g. `listing_view_page`). You can check the available templates in the `includes/templates` directory of HivePress.
			 *
			 * @hook hivepress/v1/templates/{template_name}/blocks
			 * @param {array} $blocks Template blocks.
			 * @param {object} $template Template object.
			 * @return {array} Template blocks.
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
	 * Gets template attributes.
	 *
	 * @return array
	 */
	final public function get_attributes() {
		return $this->attributes;
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
