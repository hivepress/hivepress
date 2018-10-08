<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages shortcodes.
 *
 * @class Shortcode
 */
class Shortcode extends Component {

	/**
	 * Array of shortcodes.
	 *
	 * @var array
	 */
	private $shortcodes = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Initialize shortcodes.
		add_action( 'hivepress/component/init_shortcodes', [ $this, 'init_shortcodes' ] );

		// Add shortcodes.
		add_action( 'init', [ $this, 'add_shortcodes' ] );
	}

	/**
	 * Initializes shortcodes.
	 *
	 * @param array $shortcodes
	 */
	public function init_shortcodes( $shortcodes ) {
		$this->shortcodes = array_merge( $this->shortcodes, $shortcodes );
	}

	/**
	 * Adds shortcodes.
	 */
	public function add_shortcodes() {
		foreach ( $this->shortcodes as $shortcode_id => $shortcode ) {
			add_shortcode( 'hivepress_' . $shortcode_id, [ $this, 'render_' . $shortcode_id ] );
		}
	}

	/**
	 * Routes component functions.
	 *
	 * @param string $name
	 * @param array  $args
	 */
	public function __call( $name, $args ) {
		parent::__call( $name, $args );

		// Render shortcode.
		if ( strpos( $name, 'render_' ) === 0 ) {

			// Get shortcode ID.
			$shortcode_id = str_replace( 'render_', '', $name );

			// Render shortcode HTML.
			return apply_filters( "hivepress/shortcode/shortcode_html/{$shortcode_id}", '', reset( $args ) );
		}
	}
}
