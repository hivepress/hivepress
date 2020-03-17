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
	'settings'                                  => esc_html__( 'Settings', 'hivepress' ),
	'details'                                   => esc_html__( 'Details', 'hivepress' ),
	'emails'                                    => esc_html__( 'Emails', 'hivepress' ),
	'submission'                                => esc_html__( 'Submission', 'hivepress' ),
	'moderation'                                => esc_html__( 'Moderation', 'hivepress' ),
	'expiration'                                => esc_html__( 'Expiration', 'hivepress' ),
	'free'                                      => esc_html__( 'Free', 'hivepress' ),
	'overview'                                  => esc_html__( 'Overview', 'hivepress' ),
	'storage'                                   => esc_html__( 'Storage', 'hivepress' ),
	'storage_period'                            => esc_html__( 'Storage Period', 'hivepress' ),
	'api_key'                                   => esc_html__( 'API Key', 'hivepress' ),
	'client_id'                                 => esc_html__( 'Client ID', 'hivepress' ),
	'app_id'                                    => esc_html__( 'App ID', 'hivepress' ),
	'return_to_my_account'                      => esc_html__( 'Return to My Account', 'hivepress' ),
	'columns_number'                            => esc_html_x( 'Columns', 'quantity', 'hivepress' ),
	'items_number'                              => esc_html_x( 'Number', 'quantity', 'hivepress' ),
	'by_items_number'                           => esc_html_x( 'Item Count', 'sort order', 'hivepress' ),
	'by_name'                                   => esc_html_x( 'Name', 'sort order', 'hivepress' ),
	'by_date_added'                             => esc_html_x( 'Date Added', 'sort order', 'hivepress' ),
	'by_date_registered'                        => esc_html_x( 'Date Registered', 'sort order', 'hivepress' ),
	'by_title'                                  => esc_html_x( 'Title', 'sort order', 'hivepress' ),
	'by_random'                                 => esc_html_x( 'Random', 'sort order', 'hivepress' ),
	'sort_order'                                => esc_html_x( 'Order', 'sort', 'hivepress' ),
	'ecommerce_product'                         => sprintf( esc_html__( '%s Product', 'hivepress' ), 'WooCommerce' ),
	'these_tokens_are_available'                => esc_html__( 'The following tokens are available: %s.', 'hivepress' ),
	'extension_is_incompatible_with_core'       => esc_html__( '%s extension is incompatible with the current HivePress version, please update HivePress or deactivate it.', 'hivepress' ),

	// Listings.
	'listing'                                   => esc_html__( 'Listing', 'hivepress' ),
	'listings'                                  => esc_html__( 'Listings', 'hivepress' ),
	'all_listings'                              => esc_html__( 'All Listings', 'hivepress' ),
	'related_listings'                          => esc_html__( 'Related Listings', 'hivepress' ),
	'n_listings'                                => _n_noop( '%s Listing', '%s Listings', 'hivepress' ),
	'listings_by_vendor'                        => esc_html__( 'Listings by %s', 'hivepress' ),
	'featuring_of_listings'                     => esc_html__( 'Featuring of Listings', 'hivepress' ),
	'featured_listings'                         => esc_html__( 'Featured Listings', 'hivepress' ),
	'view_listings'                             => esc_html__( 'View Listings', 'hivepress' ),
	'search_listings'                           => esc_html__( 'Search Listings', 'hivepress' ),
	'no_listings_found'                         => esc_html__( 'No listings found.', 'hivepress' ),
	'listing_categories'                        => esc_html__( 'Listing Categories', 'hivepress' ),
	'listing_search_form'                       => esc_html__( 'Listing Search Form', 'hivepress' ),
	'listing_expiration'                        => esc_html__( 'Listing Expiration', 'hivepress' ),
	'submit_listing'                            => esc_html__( 'Submit Listing', 'hivepress' ),
	'renew_listing'                             => esc_html__( 'Renew Listing', 'hivepress' ),
	'add_listing'                               => esc_html__( 'Add Listing', 'hivepress' ),
	'view_listing'                              => esc_html__( 'View Listing', 'hivepress' ),
	'edit_listing'                              => esc_html__( 'Edit Listing', 'hivepress' ),
	'delete_listing'                            => esc_html__( 'Delete Listing', 'hivepress' ),
	'report_listing'                            => esc_html__( 'Report Listing', 'hivepress' ),
	'listing_submitted'                         => esc_html__( 'Listing Submitted', 'hivepress' ),
	'listing_approved'                          => esc_html__( 'Listing Approved', 'hivepress' ),
	'listing_rejected'                          => esc_html__( 'Listing Rejected', 'hivepress' ),
	'listing_expired'                           => esc_html__( 'Listing Expired', 'hivepress' ),
	'listing_renewed'                           => esc_html__( 'Listing Renewed', 'hivepress' ),
	'listing_updated'                           => esc_html__( 'Listing Updated', 'hivepress' ),
	'listing_reported'                          => esc_html__( 'Listing Reported', 'hivepress' ),
	'listing_has_been_submitted'                => esc_html__( 'Thank you! Your listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress' ),
	'listing_has_been_renewed'                  => esc_html__( 'Thank you! Your listing "%s" has been renewed and will appear at the top of the page.', 'hivepress' ),
	'listing_has_been_reported'                 => esc_html__( 'Listing has been reported.', 'hivepress' ),
	'listings_page'                             => esc_html__( 'Listings Page', 'hivepress' ),
	'listings_page_display'                     => esc_html__( 'Listings Page Display', 'hivepress' ),
	'regular_listings_per_page'                 => esc_html__( 'Regular Listings per Page', 'hivepress' ),
	'featured_listings_per_page'                => esc_html__( 'Featured Listings per Page', 'hivepress' ),
	'related_listings_per_page'                 => esc_html__( 'Related Listings per Page', 'hivepress' ),
	'mark_listing_as_verified'                  => esc_html__( 'Mark this listing as verified', 'hivepress' ),
	'make_listing_featured'                     => esc_html__( 'Make this listing featured', 'hivepress' ),
	'make_listings_featured'                    => esc_html__( 'Make listings featured', 'hivepress' ),
	'display_only_featured_listings'            => esc_html__( 'Display only featured listings', 'hivepress' ),
	'confirm_listing_deletion'                  => esc_html__( 'Are you sure you want to permanently delete this listing?', 'hivepress' ),
	'manually_approve_listings'                 => esc_html__( 'Manually approve new listings', 'hivepress' ),
	'allow_submitting_listings'                 => esc_html__( 'Allow submitting new listings', 'hivepress' ),
	'set_number_of_days_until_listing_expires'  => esc_html__( 'Set the number of days after which a listing expires.', 'hivepress' ),
	'set_date_on_which_listing_expired'         => esc_html__( 'Set a date on which the listing will expire.', 'hivepress' ),
	'set_date_on_which_listing_not_featured'    => esc_html__( 'Set a date on which the listing will lose featured status.', 'hivepress' ),
	'choose_page_that_displays_all_listings'    => esc_html__( 'Choose a page that displays all listings.', 'hivepress' ),
	'choose_page_with_listing_submission_terms' => esc_html__( 'Choose a page with terms that user has to accept before submitting a new listing.', 'hivepress' ),
	'provide_details_to_verify_listing_report'  => esc_html__( 'Please provide details that will help us verify that this listing violates the terms of service.', 'hivepress' ),

	// Vendors.
	'vendor'                                    => esc_html__( 'Vendor', 'hivepress' ),
	'view_vendor'                               => esc_html__( 'View Vendor', 'hivepress' ),
	'add_vendor'                                => esc_html__( 'Add Vendor', 'hivepress' ),
	'edit_vendor'                               => esc_html__( 'Edit Vendor', 'hivepress' ),
	'vendors'                                   => esc_html__( 'Vendors', 'hivepress' ),
	'search_vendors'                            => esc_html__( 'Search Vendors', 'hivepress' ),
	'no_vendors_found'                          => esc_html__( 'No vendors found.', 'hivepress' ),
	'vendor_search_form'                        => esc_html__( 'Vendor Search Form', 'hivepress' ),
	'vendors_page'                              => esc_html__( 'Vendors Page', 'hivepress' ),
	'regular_vendors_per_page'                  => esc_html__( 'Vendors per Page', 'hivepress' ),
	'choose_page_that_displays_all_vendors'     => esc_html__( 'Choose a page that displays all vendors.', 'hivepress' ),

	// Categories.
	'category'                                  => esc_html__( 'Category', 'hivepress' ),
	'parent_category'                           => esc_html__( 'Parent Category', 'hivepress' ),
];
