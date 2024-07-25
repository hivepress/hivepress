<?php
/**
 * Captcha field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Google reCaptcha embed.
 */
class Captcha extends Field {

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set attributes.
		$attributes = [
			'class'        => [ 'g-recaptcha' ],
			'data-sitekey' => get_option( 'hp_recaptcha_site_key' ),
		];

		if ( 'invisible' === get_option( 'hp_recaptcha_type' ) ) {
			$attributes['data-size'] = 'invisible';
		}

		$this->attributes = hp\merge_arrays(
			$this->attributes,
			$attributes
		);

		parent::boot();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<div ' . hp\html_attributes( $this->attributes ) . '></div>';
	}
}
