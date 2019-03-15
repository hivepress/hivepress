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
class Listing_Search_Form extends Block {

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => esc_html__( 'Listing Search Form', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo add class.
		$form = new \HivePress\Forms\Listing_Search();

		$form->set_values( $_GET );

		return $form->render();
	}
}
