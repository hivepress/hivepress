<?php
/**
 * Elementor component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;
use HivePress\Integrations;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Elementor component class.
 *
 * @class Elementor
 */
final class Elementor extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Check Elementor status.
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			return;
		}

		// Register categories.
		add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

		// Register widgets.
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );

		parent::__construct( $args );
	}

	/**
	 * Registers categories.
	 *
	 * @param object $manager Category manager.
	 */
	public function register_categories( $manager ) {
		$manager->add_category(
			'hivepress',
			[
				'title' => hivepress()->get_name(),
			]
		);
	}

	/**
	 * Registers widgets.
	 */
	public function register_widgets() {
		foreach ( hivepress()->get_classes( 'blocks' ) as $block_type => $block ) {
			if ( $block::get_meta( 'label' ) ) {

				// Get slug.
				$block_slug = 'hivepress-' . hp\sanitize_slug( $block_type );

				// Register widget.
				\Elementor\Plugin::instance()->widgets_manager->register_widget_type(
					new Integrations\Elementor_Widget( [], [ 'widgetType' => $block_slug ] )
				);
			}
		}
	}
}
