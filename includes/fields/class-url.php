<?php
/**
 * URL field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * URL.
 */
class URL extends Text {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'URL', 'hivepress' ),
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'min_length' => null,
					'max_length' => null,
					'pattern'    => null,
				],
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
				'max_length' => 2048,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Sets field display template.
	 *
	 * @param string $display_template Display template.
	 */
	protected function set_display_template( $display_template ) {
		if ( strpos( $display_template, '<a ' ) === false && ! hp\has_shortcode( $display_template ) ) {
			$display_template = str_replace( '%value%', '<a href="%value%">%value%</a>', $display_template );
		}

		$this->display_template = $display_template;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = esc_url_raw( $this->value );
	}
}
