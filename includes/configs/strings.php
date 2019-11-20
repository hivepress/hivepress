<?php
/**
 * Strings configuration.
 *
 * @package HivePress\Configs
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [

	// Common.
	'settings'                                       => esc_html__( 'Settings', 'hivepress' ),
	'details'                                        => esc_html__( 'Details', 'hivepress' ),
	'moderation'                                     => esc_html__( 'Moderation', 'hivepress' ),
	'free'                                           => esc_html__( 'Free', 'hivepress' ),
	'api_key'                                        => esc_html__( 'API Key', 'hivepress' ),
	'client_id'                                      => esc_html__( 'Client ID', 'hivepress' ),
	'app_id'                                         => esc_html__( 'App ID', 'hivepress' ),
	'return_to_my_account'                           => esc_html__( 'Return to My Account', 'hivepress' ),
	'limit_exceeded'                                 => esc_html__( 'Limit Exceeded', 'hivepress' ),
	'ecommerce_product'                              => esc_html__( 'WooCommerce Product', 'hivepress' ),

	// Listings.
	'listing'                                        => esc_html__( 'Listing', 'hivepress' ),
	'submit_listing'                                 => esc_html__( 'Submit Listing', 'hivepress' ),
	'add_listing'                                    => esc_html__( 'Add Listing', 'hivepress' ),
	'view_listing'                                   => esc_html__( 'View Listing', 'hivepress' ),
	'edit_listing'                                   => esc_html__( 'Edit Listing', 'hivepress' ),
	'update_listing'                                 => esc_html__( 'Update Listing', 'hivepress' ),
	'delete_listing'                                 => esc_html__( 'Delete Listing', 'hivepress' ),
	'report_listing'                                 => esc_html__( 'Report Listing', 'hivepress' ),
	'claim_listing'                                  => esc_html__( 'Claim Listing', 'hivepress' ),
	'reply_to_listing'                               => esc_html__( 'Reply to Listing', 'hivepress' ),
	'listing_has_been_updated'                       => esc_html__( 'Listing has been updated.', 'hivepress' ),
	'listing_has_been_reported'                      => esc_html__( 'Listing has been reported.', 'hivepress' ),
	'listing_submitted'                              => esc_html__( 'Listing Submitted', 'hivepress' ),
	'listing_approved'                               => esc_html__( 'Listing Approved', 'hivepress' ),
	'listing_rejected'                               => esc_html__( 'Listing Rejected', 'hivepress' ),
	'listing_expired'                                => esc_html__( 'Listing Expired', 'hivepress' ),
	'listing_updated'                                => esc_html__( 'Listing Updated', 'hivepress' ),
	'listing_reported'                               => esc_html__( 'Listing Reported', 'hivepress' ),
	'listings'                                       => esc_html__( 'Listings', 'hivepress' ),
	'listings_page'                                  => esc_html__( 'Listings Page', 'hivepress' ),
	'listings_page_display'                          => esc_html__( 'Listings Page Display', 'hivepress' ),
	'featured_listings'                              => esc_html__( 'Featured Listings', 'hivepress' ),
	'listings_by_vendor'                             => esc_html__( 'Listings by %s', 'hivepress' ),
	'view_listings'                                  => esc_html__( 'View Listings', 'hivepress' ),
	'search_listings'                                => esc_html__( 'Search Listings', 'hivepress' ),
	'no_listings_found'                              => esc_html__( 'No Listings Found', 'hivepress' ),
	'listing_search_form'                            => esc_html__( 'Listing Search Form', 'hivepress' ),
	'listing_limit'                                  => esc_html__( 'Listing Limit', 'hivepress' ),
	'listing_expiration'                             => esc_html__( 'Listing Expiration', 'hivepress' ),
	'make_listings_featured'                         => esc_html__( 'Make listings featured', 'hivepress' ),
	'make_this_listing_featured'                     => esc_html__( 'Make this listing featured', 'hivepress' ),
	'display_only_featured_listings'                 => esc_html__( 'Display only featured listings', 'hivepress' ),
	'mark_this_listing_as_verified'                  => esc_html__( 'Mark this listing as verified', 'hivepress' ),
	'set_maximum_number_of_listing_submissions'      => esc_html__( 'Set the maximum number of listing submissions.', 'hivepress' ),
	'set_number_of_days_after_which_listing_expires' => esc_html__( 'Set the number of days after which a listing expires.', 'hivepress' ),
	'choose_page_that_displays_all_listings'         => esc_html__( 'Choose a page that displays all listings.', 'hivepress' ),

	// Listing categories.
	'listing_categories'                             => esc_html__( 'Listing Categories', 'hivepress' ),

	// Vendors.
	'vendor'                                         => esc_html__( 'Vendor', 'hivepress' ),
	'view_vendor'                                    => esc_html__( 'View Vendor', 'hivepress' ),
	'add_vendor'                                     => esc_html__( 'Add Vendor', 'hivepress' ),
	'edit_vendor'                                    => esc_html__( 'Edit Vendor', 'hivepress' ),
	'vendors'                                        => esc_html__( 'Vendors', 'hivepress' ),
	'search_vendors'                                 => esc_html__( 'Search Vendors', 'hivepress' ),
	'no_vendors_found'                               => esc_html__( 'No Vendors Found', 'hivepress' ),
];
