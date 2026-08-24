<?php
/**
 * Timestamp field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Timestamp.
 */
class Timestamp extends Date {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'       => null,
				'type'        => 'DECIMAL',
				'filterable'  => false,
				'sortable'    => false,
				'prefillable' => false,
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
				'format' => 'U',
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->multiple ) {
			$this->value = array_map( 'absint', $this->value );
		} else {
			$this->value = absint( $this->value );
		}
	}
}
