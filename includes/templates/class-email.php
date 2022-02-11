<?php
/**
 * Email template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Base email template.
 */
class Email extends Template {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'email_content' => [
						'type'   => 'part',
						'path'   => 'email/email-content',
						'_order' => 10,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
