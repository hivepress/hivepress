<?php
/**
 * HivePress uninstaller.
 *
 * @package HivePress
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $wpdb;

if ( defined( 'HP_UNINSTALL' ) && HP_UNINSTALL ) {

	// Trash pages.
	$page_ids = wp_list_pluck( $wpdb->get_results( "SELECT option_value FROM {$wpdb->options} WHERE option_name LIKE 'hp\_page\_%';" ), 'option_value' );

	foreach ( $page_ids as $page_id ) {
		wp_trash_post( absint( $page_id ) );
	}

	// Delete posts.
	$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type LIKE 'hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key LIKE 'hp\_%';" );

	// Delete comments.
	$wpdb->query( "DELETE FROM {$wpdb->comments} WHERE comment_type LIKE 'hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE meta_key LIKE 'hp\_%';" );

	// Delete terms.
	$wpdb->query( "DELETE FROM {$wpdb->term_taxonomy} WHERE taxonomy LIKE 'hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE meta_key LIKE 'hp\_%';" );

	$wpdb->query( "DELETE tr FROM {$wpdb->term_relationships} tr LEFT JOIN {$wpdb->posts} posts ON posts.ID = tr.object_id WHERE posts.ID IS NULL;" );
	$wpdb->query( "DELETE t FROM {$wpdb->terms} t LEFT JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id WHERE tt.term_id IS NULL;" );
	$wpdb->query( "DELETE tm FROM {$wpdb->termmeta} tm LEFT JOIN {$wpdb->term_taxonomy} tt ON tm.term_id = tt.term_id WHERE tt.term_id IS NULL;" );

	// Delete user meta.
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'hp\_%';" );

	// Delete options.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE 'hp\_%';" );

	// Delete transients.
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_timeout\_hp\_%';" );

	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE option_name LIKE '\_transient\_hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE option_name LIKE '\_transient\_timeout\_hp\_%';" );

	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE option_name LIKE '\_transient\_hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE option_name LIKE '\_transient\_timeout\_hp\_%';" );

	$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE option_name LIKE '\_transient\_hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->termmeta} WHERE option_name LIKE '\_transient\_timeout\_hp\_%';" );

	$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE option_name LIKE '\_transient\_hp\_%';" );
	$wpdb->query( "DELETE FROM {$wpdb->commentmeta} WHERE option_name LIKE '\_transient\_timeout\_hp\_%';" );

	// Flush cache.
	wp_cache_flush();
}
