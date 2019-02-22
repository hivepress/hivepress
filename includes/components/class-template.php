<?php
/**
 * Template component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template component class.
 *
 * @class Template
 */
final class Template {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// Manage rewrite rules.
		add_action( 'init', [ $this, 'add_rewrite_rules' ] );
		add_action( 'hivepress/activate', [ $this, 'flush_rewrite_rules' ] );

		if ( ! is_admin() ) {

			// Set page title.
			add_filter( 'document_title_parts', [ $this, 'set_page_title' ] );

			// Set page template.
			add_filter( 'template_include', [ $this, 'set_page_template' ] );
		}
	}

	/**
	 * Adds rewrite rules.
	 */
	public function add_rewrite_rules() {
		foreach ( hivepress()->get_controllers() as $controller ) {
			if ( $controller->get_url() ) {

				// Get rewrite tags.
				preg_match_all( '/<([a-z_]+)>/i', $controller->get_url(), $rewrite_tags );

				$rewrite_tags = array_filter( array_map( 'sanitize_title', array_map( 'current', $rewrite_tags ) ) );

				// Get query string.
				if ( empty( $rewrite_tags ) ) {
					$rewrite_tag  = strtolower( ( new \ReflectionClass( $controller ) )->getShortName() );
					$rewrite_tags = [ $rewrite_tag ];

					$query_string = hp_prefix( $rewrite_tag ) . '=1';
				} else {
					$query_string = implode(
						'&',
						array_map(
							function( $rewrite_tag ) {
								return hp_prefix( $rewrite_tag ) . '={$matches[' . $rewrite_tag . ']}';
							},
							$rewrite_tags
						)
					);
				}

				// Add rewrite rule.
				add_rewrite_rule( $controller->get_url(), 'index.php?' . $query_string, 'top' );

				// Add rewrite tags.
				foreach ( $rewrite_tags as $rewrite_tag ) {
					add_rewrite_tag( '%' . hp_prefix( $rewrite_tag ) . '%', '([^&]+)' );
				}
			}
		}
	}

	/**
	 * Flushes rewrite rules.
	 */
	public function flush_rewrite_rules() {
		update_option( 'rewrite_rules', false );
		flush_rewrite_rules();
	}

	/**
	 * Sets page title.
	 *
	 * @param array $parts Title parts.
	 * @return string
	 */
	public function set_page_title( $parts ) {
		// todo.
		return $parts;
	}

	/**
	 * Sets page template.
	 *
	 * @param array $template Template file.
	 * @return string
	 */
	public function set_page_template( $template ) {
		// todo.
		$controllers = hivepress()->get_controllers();

		foreach ( $controllers as $controller ) {
			if ( $controller->match() ) {
				get_header();
				echo $controller->render();
				get_footer();
				die();
			}
		}

		return $template;
	}
}
