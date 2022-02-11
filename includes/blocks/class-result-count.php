<?php
/**
 * Result count block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the query result count.
 */
class Result_Count extends Block {

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		global $wp_query;

		$output = '';

		if ( $wp_query->found_posts || hivepress()->request->get_context( 'featured_ids' ) ) {
			$output = '<div class="hp-result-count">';

			// Get first result.
			$first_result = 1;

			if ( hivepress()->request->get_page_number() > 1 ) {
				$first_result = $wp_query->query_vars['posts_per_page'] * ( hivepress()->request->get_page_number() - 1 ) + 1;
			}

			// Get last result.
			$last_result = $first_result + $wp_query->post_count - 1;

			// Get total results.
			$total_results = $wp_query->found_posts;

			// Add featured results.
			if ( hivepress()->request->get_context( 'featured_ids' ) ) {
				$featured_results = count( hivepress()->request->get_context( 'featured_ids' ) );

				$last_result   += $featured_results;
				$total_results += $featured_results;
			}

			/* translators: 1: results range start, 2: results range end, 3: total results. */
			$output .= sprintf( esc_html__( 'Showing %1$s-%2$s of %3$s results', 'hivepress' ), number_format_i18n( $first_result ), number_format_i18n( $last_result ), number_format_i18n( $total_results ) );

			$output .= '</div>';
		}

		return $output;
	}
}
