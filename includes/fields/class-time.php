<?php
/**
 * Time field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Time field class.
 *
 * @class Time
 */
class Time extends Number {

	/**
	 * Time display format.
	 *
	 * @var string
	 */
	protected $display_format;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => null,
				'filterable' => false,
				'sortable'   => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'display_type' => 'number',
				'min_value'    => 0,
				'max_value'    => DAY_IN_SECONDS - 1,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set display format.
		if ( is_null( $this->display_format ) ) {
			$this->display_format = get_option( 'time_format' );
		}

		$attributes['data-display-format'] = $this->display_format;

		// Set component.
		$attributes['data-component'] = 'time';

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		Field::boot();
	}
}
