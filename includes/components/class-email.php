<?php
/**
 * Email component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Email component class.
 *
 * @class Email
 */
final class Email extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {
		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_email_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_hp_email_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

			// Disable editor settings.
			add_filter( 'wp_editor_settings', [ $this, 'disable_editor_settings' ], 10, 2 );
		}

		parent::__construct( $args );
	}

	/**
	 * Adds admin columns.
	 *
	 * @param array $columns Columns.
	 * @return array
	 */
	public function add_admin_columns( $columns ) {
		unset( $columns['date'] );

		return array_merge(
			$columns,
			[
				'title' => esc_html__( 'Subject', 'hivepress' ),
				'event' => esc_html__( 'Event', 'hivepress' ),
			]
		);
	}

	/**
	 * Renders admin columns.
	 *
	 * @param string $column Column name.
	 * @param int    $email_id Email ID.
	 */
	public function render_admin_columns( $column, $email_id ) {

		// Check column.
		if ( 'event' !== $column ) {
			return;
		}

		$output = '&mdash;';

		// Get email.
		$email = hp\get_array_value( hivepress()->get_classes( 'emails' ), get_post_field( 'post_name' ) );

		// Set label.
		if ( $email && $email::get_meta( 'label' ) ) {
			$output = $email::get_meta( 'label' );
		}

		echo wp_kses_data( $output );
	}

	/**
	 * Disables editor settings.
	 *
	 * @param array  $settings Settings.
	 * @param string $editor Editor ID.
	 * @return array
	 */
	public function disable_editor_settings( $settings, $editor ) {
		if ( 'content' === $editor ) {
			$screen = get_current_screen();

			if ( $screen && 'hp_email' === $screen->id ) {
				$settings['media_buttons'] = false;
				$settings['teeny']         = true;
			}
		}

		return $settings;
	}
}
