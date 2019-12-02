<?php
/**
 * Listing search form block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search form block class.
 *
 * @class Listing_Search_Form
 */
class Listing_Search_Form extends Form {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => hivepress()->translator->get_string( 'listing_search_form' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'form' => 'listing_search',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-form--wide', 'hp-block' ],
			]
		);

		parent::bootstrap();
	}
}
