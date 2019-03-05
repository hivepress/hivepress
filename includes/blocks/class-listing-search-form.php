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
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'title' => esc_html__( 'Listing Search Form', 'hivepress' ),
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$form = new \HivePress\Forms\Listing_Search( $this->get_attributes() );

		$form->set_values( $_GET );

		return $form->render();
	}
}
