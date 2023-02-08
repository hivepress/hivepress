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
 * Implements integration with Elementor.
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
		if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '<=' ) ) {
			add_action( 'elementor/widgets/widgets_registered', [ $this, 'register_widgets' ] );
		} else {
			add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		}

		// Set preview mode.
		add_filter( 'hivepress/v1/components/editor/preview', [ $this, 'set_preview_mode' ] );

		// Add icon styles.
		add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'add_icon_styles' ] );

		parent::__construct( $args );
	}

	/**
	 * Registers widget categories.
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
				if ( version_compare( ELEMENTOR_VERSION, '3.5.0', '<=' ) ) {
					\Elementor\Plugin::instance()->widgets_manager->register_widget_type(
						new Integrations\Elementor_Widget( [], [ 'widgetType' => $block_slug ] )
					);
				} else {
					\Elementor\Plugin::instance()->widgets_manager->register(
						new Integrations\Elementor_Widget( [], [ 'widgetType' => $block_slug ] )
					);
				}
			}
		}
	}

	/**
	 * Sets preview mode.
	 *
	 * @param bool $preview Is preview mode?
	 * @return bool
	 */
	public function set_preview_mode( $preview ) {
		return $preview || \Elementor\Plugin::$instance->editor->is_edit_mode();
	}

	/**
	 * Adds icon styles.
	 */
	public function add_icon_styles() {

		// Get icon URL.
		$icon_url = hivepress()->get_url() . '/assets/images/logo-dark.svg';

		// Add icon styles.
		wp_add_inline_style(
			'elementor-icons',
			'.eicon-hivepress {
				display: block;
				margin: 0 auto;
				width: 28px;
				height: 28px;
				background: url("' . esc_url( $icon_url ) . '") center center no-repeat;
				background-size: 28px;
			}'
		);
	}
}
