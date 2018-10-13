<?php
namespace HivePress;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Manages debugging.
 *
 * @class Debug
 */
class Debug extends Component {

	/**
	 * Array of styles.
	 *
	 * @var array
	 */
	private $styles = [];

	/**
	 * Array of admin styles.
	 *
	 * @var array
	 */
	private $admin_styles = [];

	/**
	 * Array of scripts.
	 *
	 * @var array
	 */
	private $scripts = [];

	/**
	 * Array of admin scripts.
	 *
	 * @var array
	 */
	private $admin_scripts = [];

	/**
	 * Class constructor.
	 *
	 * @param array $settings
	 */
	public function __construct( $settings ) {
		parent::__construct( $settings );

		// Initialize styles.
		add_action( 'hivepress/component/init_styles', [ $this, 'init_styles' ], 10, 2 );
		add_action( 'hivepress/component/init_admin_styles', [ $this, 'init_admin_styles' ], 10, 2 );

		// Initialize scripts.
		add_action( 'hivepress/component/init_scripts', [ $this, 'init_scripts' ], 10, 2 );
		add_action( 'hivepress/component/init_admin_scripts', [ $this, 'init_admin_scripts' ], 10, 2 );

		// Enqueue styles.
		add_action( 'wp_head', [ $this, 'enqueue_styles' ] );
		add_action( 'admin_head', [ $this, 'enqueue_admin_styles' ] );

		// Enqueue scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		// Log email content.
		add_filter( 'wp_mail', [ $this, 'log_email_content' ] );
	}

	/**
	 * Initializes styles.
	 *
	 * @param array  $styles
	 * @param string $component_name
	 */
	public function init_styles( $styles, $component_name ) {
		$this->styles = array_merge(
			$this->styles,
			array_combine(
				array_map(
					function( $style_id ) use ( $component_name ) {
						return $component_name . '__' . $style_id;
					},
					array_keys( $styles )
				),
				$styles
			)
		);
	}

	/**
	 * Initializes admin styles.
	 *
	 * @param array  $styles
	 * @param string $component_name
	 */
	public function init_admin_styles( $styles, $component_name ) {
		$this->admin_styles = array_merge(
			$this->admin_styles,
			array_combine(
				array_map(
					function( $style_id ) use ( $component_name ) {
						return $component_name . '__' . $style_id;
					},
					array_keys( $styles )
				),
				$styles
			)
		);
	}

	/**
	 * Initializes scripts.
	 *
	 * @param array  $scripts
	 * @param string $component_name
	 */
	public function init_scripts( $scripts, $component_name ) {
		$this->scripts = array_merge(
			$this->scripts,
			array_combine(
				array_map(
					function( $script_id ) use ( $component_name ) {
						return $component_name . '__' . $script_id;
					},
					array_keys( $scripts )
				),
				$scripts
			)
		);
	}

	/**
	 * Initializes admin scripts.
	 *
	 * @param array  $scripts
	 * @param string $component_name
	 */
	public function init_admin_scripts( $scripts, $component_name ) {
		$this->admin_scripts = array_merge(
			$this->admin_scripts,
			array_combine(
				array_map(
					function( $script_id ) use ( $component_name ) {
						return $component_name . '__' . $script_id;
					},
					array_keys( $scripts )
				),
				$scripts
			)
		);
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {
		$output = '';

		foreach ( $this->styles as $style ) {
			if ( isset( $style['src'] ) && strpos( $style['src'], 'frontend' ) !== false ) {
				$url = str_replace( '.min', '', str_replace( '.css', '.less', $style['src'] ) );

				$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( $url ) . '" />';
			}
		}

		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.7.1/less.min.js" ></script>';

		echo $output;
	}

	/**
	 * Enqueues admin styles.
	 */
	public function enqueue_admin_styles() {
		$output = '';

		foreach ( $this->admin_styles as $style ) {
			if ( isset( $style['src'] ) && strpos( $style['src'], 'backend' ) !== false ) {
				$url = str_replace( '.min', '', str_replace( '.css', '.less', $style['src'] ) );

				$output .= '<link rel="stylesheet/less" type="text/css" href="' . esc_url( $url ) . '" />';
			}
		}

		$output .= '<script src="//cdnjs.cloudflare.com/ajax/libs/less.js/3.7.1/less.min.js" ></script>';

		echo $output;
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {
		foreach ( $this->scripts as $script ) {
			if ( isset( $script['src'] ) && strpos( $script['src'], 'frontend' ) !== false ) {
				$script['src'] = str_replace( '.min', '', $script['src'] );

				$this->enqueue_script( $script );
			}
		}
	}

	/**
	 * Enqueues admin scripts.
	 */
	public function enqueue_admin_scripts() {
		foreach ( $this->admin_scripts as $script ) {
			if ( isset( $script['src'] ) && strpos( $script['src'], 'backend' ) !== false ) {
				$script['src'] = str_replace( '.min', '', $script['src'] );

				$this->enqueue_script( $script );
			}
		}
	}

	/**
	 * Logs email content.
	 *
	 * @param array        $args
	 * @param return array
	 */
	public function log_email_content( $args ) {
		error_log( ' ' );
		error_log( $args['to'] );
		error_log( $args['subject'] );
		error_log( $args['message'] );
		error_log( ' ' );

		return $args;
	}
}
