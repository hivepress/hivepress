<?php
/**
 * Media component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Media component class.
 *
 * @class Media
 */
final class Media {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Add image sizes.
		add_action( 'init', [ $this, 'add_image_sizes' ] );

		// Enqueue styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_styles' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

		// Enqueue scripts.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		// Filter scripts.
		add_filter( 'script_loader_tag', [ $this, 'filter_script' ], 10, 2 );

		// Register API routes.
		add_action( 'rest_api_init', [ $this, 'register_api_routes' ] );
	}

	/**
	 * Adds image sizes.
	 */
	public function add_image_sizes() {
		foreach ( hivepress()->get_config( 'image_sizes' ) as $image_size => $image_size_args ) {
			add_image_size( hp_prefix( $image_size ), $image_size_args['width'], hp_get_array_value( $image_size_args, 'height', 9999 ), hp_get_array_value( $image_size_args, 'crop', false ) );
		}
	}

	/**
	 * Enqueues styles.
	 */
	public function enqueue_styles() {

		// Get styles.
		$styles = hivepress()->get_config( 'styles' );

		// Filter styles.
		$styles = array_filter(
			$styles,
			function( $style ) {
				return ! is_admin() xor hp_get_array_value( $style, 'admin', false );
			}
		);

		// Enqueue styles.
		foreach ( $styles as $style ) {
			wp_enqueue_style( $style['handle'], $style['src'], hp_get_array_value( $style, 'deps', [] ), hp_get_array_value( $style, 'version', HP_CORE_VERSION ) );
		}
	}

	/**
	 * Enqueues scripts.
	 */
	public function enqueue_scripts() {

		// Get scripts.
		$scripts = hivepress()->get_config( 'scripts' );

		// Filter scripts.
		$scripts = array_filter(
			$scripts,
			function( $script ) {
				return ! is_admin() xor hp_get_array_value( $script, 'admin', false );
			}
		);

		// Enqueue scripts.
		foreach ( $scripts as $script ) {
			wp_enqueue_script( $script['handle'], $script['src'], hp_get_array_value( $script, 'deps', [] ), hp_get_array_value( $script, 'version', HP_CORE_VERSION ), hp_get_array_value( $script, 'in_footer', true ) );

			// Add script data.
			if ( isset( $script['data'] ) ) {
				wp_localize_script( $script['handle'], lcfirst( str_replace( ' ', '', ucwords( str_replace( '-', ' ', $script['handle'] ) ) ) ) . 'Data', $script['data'] );
			}
		}
	}

	/**
	 * Filters script HTML.
	 *
	 * @param string $tag Script tag.
	 * @param string $handle Script handle.
	 * @return string
	 */
	public function filter_script( $tag, $handle ) {

		// Set attributes.
		$atts = [ 'async', 'defer' ];

		foreach ( $atts as $att ) {
			if ( wp_scripts()->get_data( $handle, $att ) ) {
				$tag = str_replace( '></', ' ' . $att . '></', $tag );
			}
		}

		return $tag;
	}

	/**
	 * Registers API routes.
	 */
	public function register_api_routes() {

		// Upload file.
		register_rest_route(
			'hivepress/v1',
			'/files',
			[
				'methods'  => 'POST',
				'callback' => [ $this, 'upload_file' ],
			]
		);

		// Delete file.
		register_rest_route(
			'hivepress/v1',
			'/files/(?P<id>[0-9]+)',
			[
				'methods'  => 'DELETE',
				'callback' => [ $this, 'delete_file' ],
			]
		);
	}

	/**
	 * Uploads file.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function upload_file( $request ) {
		$response = [
			'success' => false,
		];

		if ( is_user_logged_in() ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			// Get form class.
			$form_class = '\HivePress\Forms\\' . $request->get_param( 'form' );

			if ( class_exists( $form_class ) ) {

				// Create form.
				$form = new $form_class();

				// Get field.
				$field = hp_get_array_value( $form->get_fields(), $request->get_param( 'field' ) );

				if ( ! is_null( $field ) && $field->get_type() === 'file_upload' ) {
					if ( $field->validate() ) {

						// Upload file.
						$attachment_id = media_handle_upload( $request->get_param( 'field' ) );

						if ( ! is_wp_error( $attachment_id ) ) {

							// Get file URL.
							$attachment_url = wp_get_attachment_image_src( $attachment_id, 'thumbnail' );

							if ( false !== $attachment_url ) {
								$attachment_url = reset( $attachment_url );
							}

							// Set response.
							$response = [
								'success' => true,
								'id'      => $attachment_id,
								'url'     => $attachment_url,
							];
						} else {
							$response['errors'] = [ esc_html__( 'Error uploading file.', 'hivepress' ) ];
						}
					} else {
						$response['errors'] = $field->get_errors();
					}
				}
			}
		}

		return $response;
	}

	/**
	 * Deletes file.
	 *
	 * @param WP_REST_Request $request API request.
	 * @return mixed
	 */
	public function delete_file( $request ) {
		if ( is_user_logged_in() ) {

			// Get attachment ID.
			$attachment_id = hp_get_post_id(
				[
					'post_type'   => 'attachment',
					'post_status' => 'any',
					'post__in'    => [ absint( $request->get_param( 'id' ) ) ],
				]
			);

			if ( 0 !== $attachment_id ) {

				// Get user IDs.
				$user_ids = [
					absint( get_post_field( 'post_author', $attachment_id ) ),
					absint( get_post_field( 'post_author', get_post_field( 'post_parent', $attachment_id ) ) ),
				];

				if ( in_array( get_curren_user_id(), $user_ids, true ) ) {

					// Delete attachment.
					wp_delete_attachment( $attachment_id, true );
				}
			}
		}
	}
}
