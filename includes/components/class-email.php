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
 * Handles emails.
 */
final class Email extends Component {

	/**
	 * Class constructor.
	 *
	 * @param array $args Component arguments.
	 */
	public function __construct( $args = [] ) {

		// Set email content.
		add_filter( 'hivepress/v1/emails/email', [ $this, 'set_email_content' ], 10, 2 );

		// Register integrations.
		add_action( 'plugins_loaded', [ $this, 'register_integrations' ] );

		if ( is_admin() ) {

			// Manage admin columns.
			add_filter( 'manage_hp_email_posts_columns', [ $this, 'add_admin_columns' ] );
			add_action( 'manage_hp_email_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

			// Disable editor settings.
			add_filter( 'wp_editor_settings', [ $this, 'disable_editor_settings' ], 10, 2 );

			// Render email details.
			add_filter( 'hivepress/v1/meta_boxes/email_details', [ $this, 'render_email_details' ] );

			// Set email defaults.
			add_action( 'post_updated', [ $this, 'set_email_defaults' ], 10, 3 );
		}

		// Add object specific tokens.
		add_filter( 'hivepress/v1/emails/email/meta', [ $this, 'alter_email_meta' ] );

		parent::__construct( $args );
	}

	/**
	 * Add object specific tokens.
	 *
	 * @param array $meta Email meta.
	 * @return array
	 */
	public function alter_email_meta( $meta ) {
		foreach ( hp\get_array_value( $meta, 'tokens', [] ) as $name => $args ) {

			// Get model name.
			$model_name = hp\get_array_value( (array) $args, 'model' );

			if ( ! $model_name ) {
				continue;
			}

			// Get class object.
			$class = hp\create_class_instance( 'HivePress\Models\\' . $model_name );

			if ( ! $class ) {
				continue;
			}

			// Get model fields.
			$model_fields = $class->_get_fields();

			if ( ! $model_fields ) {
				continue;
			}

			foreach ( $model_fields as $field_name => $field_args ) {
				if ( $field_args->get_arg( '_model' ) ) {
					continue;
				}

				// Add token.
				$meta['tokens'][] = $name . '.' . $field_name;
			}
		}

		return $meta;
	}

	/**
	 * Sets email content.
	 *
	 * @param array  $args Email arguments.
	 * @param object $email Email object.
	 * @return array
	 */
	public function set_email_content( $args, $email ) {
		if ( $email::get_meta( 'label' ) && ! hp\get_array_value( $args, 'default' ) ) {

			// Get content.
			$content = get_page_by_path( $email::get_meta( 'name' ), OBJECT, 'hp_email' );

			if ( $content && 'publish' === $content->post_status ) {

				// Set subject.
				if ( $content->post_title ) {
					$args['subject'] = $content->post_title;
				}

				// Set body.
				$args['body'] = apply_filters( 'the_content', $content->post_content );
			}
		}

		return $args;
	}

	/**
	 * Registers integrations.
	 */
	public function register_integrations() {
		if ( hp\is_plugin_active( 'mc4wp' ) ) {
			mc4wp( 'integrations' )->register_integration( 'hivepress', '\HivePress\Integrations\Mailchimp', false );
		}
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
				'title'     => esc_html__( 'Subject', 'hivepress' ),
				'event'     => esc_html__( 'Event', 'hivepress' ),
				'recipient' => esc_html__( 'Recipient', 'hivepress' ),
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
		$output = '';

		// Get email.
		$email = hp\get_array_value( hivepress()->get_classes( 'emails' ), get_post_field( 'post_name' ) );

		if ( ! $email ) {
			return;
		}

		// Render output.
		if ( 'event' === $column ) {
			$output = $email::get_meta( 'label' );
		} elseif ( 'recipient' === $column ) {
			$output = $email::get_meta( 'recipient' );
		}

		if ( $output ) {
			echo wp_kses_data( $output );
		}
	}

	/**
	 * Disables email editor settings.
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

	/**
	 * Renders email details for admins.
	 *
	 * @param array $meta_box Meta box arguments.
	 * @return array
	 */
	public function render_email_details( $meta_box ) {

		// Get email.
		$email = hp\get_array_value( hivepress()->get_classes( 'emails' ), get_post_field( 'post_name' ) );

		if ( $email && $email::get_meta( 'label' ) ) {
			$output = '';

			if ( $email::get_meta( 'description' ) ) {
				$output .= $email::get_meta( 'description' ) . ' ';
			}

			// Get tokens
			$tokens = array_filter(
				(array) $email::get_meta( 'tokens' ),
				function( $token ) {
					return ! is_array( $token );
				}
			);

			if ( $tokens ) {
				$output .= sprintf( hivepress()->translator->get_string( 'these_tokens_are_available' ), '<code>%' . implode( '%</code>, <code>%', $tokens ) . '%</code>' );
			}

			if ( $output ) {
				$meta_box['blocks']['email_details'] = [
					'type'    => 'content',
					'content' => '<p>' . trim( $output ) . '</p>',
					'_order'  => 10,
				];
			}
		}

		return $meta_box;
	}

	/**
	 * Sets email defaults.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post Post object.
	 * @param WP_Post $old_post Old post object.
	 */
	public function set_email_defaults( $post_id, $post, $old_post ) {

		// Check post.
		if ( 'hp_email' !== $post->post_type || $post->post_title || $post->post_name === $old_post->post_name ) {
			return;
		}

		// Create email.
		$email = hp\create_class_instance( '\HivePress\Emails\\' . sanitize_key( $post->post_name ), [ [ 'default' => true ] ] );

		if ( ! $email || ! $email->get_body() ) {
			return;
		}

		// Set defaults.
		wp_update_post(
			[
				'ID'           => $post_id,
				'post_title'   => $email->get_subject(),
				'post_content' => $email->get_body(),
			]
		);
	}
}
