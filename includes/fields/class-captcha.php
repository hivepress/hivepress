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
 * Captcha field class.
 *
 * @class Captcha
 */
class Captcha extends Field {

	/**
	 * Field meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set class.
		$attributes['class'] = [ 'g-recaptcha' ];

		// Set site key.
		$attributes['data-sitekey'] = get_option( 'hp_recaptcha_site_key' );

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
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
