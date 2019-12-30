<?php
/**
 * Search field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Search field class.
 *
 * @class Search
 */
class Search extends Text {

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
}
