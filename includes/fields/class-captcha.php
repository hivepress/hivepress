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
			'data-sitekey' => get_option( 'hp_recaptcha_site_key' ),
			'class'        => [ 'recaptcha' ],
		];

		// Check reCaptcha version.
		if ( get_option( 'hp_recaptcha_version' ) === 'v3' ) {
			$attributes['name'] = 'g-recaptcha-response';
		} else {
			$attributes['class'][] = 'g-recaptcha';
		}

		// Set attributes.
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

		// Check reCaptcha version.
		if ( get_option( 'hp_recaptcha_version' ) === 'v3' ) {
			$output = '<input type="hidden" ' . hp\html_attributes( $this->attributes ) . '>';
		} else {
			$output = '<div ' . hp\html_attributes( $this->attributes ) . '></div>';
		}

		return $output;
	}
}
