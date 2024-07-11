<?php
/**
 * Email details block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders email details.
 */
class Email_Details extends Content {

	/**
	 * Email event.
	 *
	 * @var string
	 */
	protected $event;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Set output.
		$output = '';

		// Get email.
		$email = hp\get_array_value( hivepress()->get_classes( 'emails' ), $this->event );

		if ( ! $email || ! $email::get_meta( 'label' ) ) {
			return $output;
		}

		// Set content.
		$content = '';

		if ( $email::get_meta( 'description' ) ) {
			$content .= $email::get_meta( 'description' ) . ' ';
		}

		if ( $email::get_meta( 'tokens' ) ) {
			$content .= sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '<code>%' . implode( '%</code>, <code>%', $email::get_meta( 'tokens' ) ) . '%</code>' );
		}

		if ( ! $content ) {
			return $output;
		}

		$this->content = $content;

		return parent::render();
	}
}
