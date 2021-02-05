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
	'display_noun'                              => esc_html_x( 'Display', 'noun', 'hivepress' ),
	'search_noun'                               => esc_html_x( 'Search', 'noun', 'hivepress' ),
	'search'                                    => esc_html__( 'Search', 'hivepress' ),
	'filter'                                    => esc_html__( 'Filter', 'hivepress' ),
	'sort_by'                                   => esc_html__( 'Sort by', 'hivepress' ),
	'keywords'                                  => esc_html__( 'Keywords', 'hivepress' ),
	'title'                                     => esc_html__( 'Title', 'hivepress' ),
	'name'                                      => esc_html__( 'Name', 'hivepress' ),
	'date'                                      => esc_html__( 'Date', 'hivepress' ),
	'images'                                    => esc_html__( 'Images', 'hivepress' ),
	'select_images'                             => esc_html__( 'Select Images', 'hivepress' ),
	'description'                               => esc_html__( 'Description', 'hivepress' ),
	'details'                                   => esc_html__( 'Details', 'hivepress' ),
	'emails'                                    => esc_html__( 'Emails', 'hivepress' ),
	'editing'                                   => esc_html__( 'Editing', 'hivepress' ),
	'submission'                                => esc_html__( 'Submission', 'hivepress' ),
	'moderation'                                => esc_html__( 'Moderation', 'hivepress' ),
	'expiration'                                => esc_html__( 'Expiration', 'hivepress' ),
	'expiration_period'                         => esc_html__( 'Expiration Period', 'hivepress' ),
	'expiration_date'                           => esc_html__( 'Expiration Date', 'hivepress' ),
	'default_fields'                            => esc_html__( 'Default Fields', 'hivepress' ),
	'free'                                      => esc_html__( 'Free', 'hivepress' ),
	'price'                                     => esc_html__( 'Price', 'hivepress' ),
	'overview'                                  => esc_html__( 'Overview', 'hivepress' ),
	'storage'                                   => esc_html__( 'Storage', 'hivepress' ),
	'storage_period'                            => esc_html__( 'Storage Period', 'hivepress' ),
	'api_key'                                   => esc_html__( 'API Key', 'hivepress' ),
	'client_id'                                 => esc_html__( 'Client ID', 'hivepress' ),
	'client_secret'                             => esc_html__( 'Client Secret', 'hivepress' ),
	'redirect_url'                              => esc_html__( 'Redirect URL', 'hivepress' ),
	'app_id'                                    => esc_html__( 'App ID', 'hivepress' ),
	'authorization'                             => esc_html__( 'Authorization', 'hivepress' ),
	'return_to_my_account'                      => esc_html__( 'Return to My Account', 'hivepress' ),
	'save_changes'                              => esc_html__( 'Save Changes', 'hivepress' ),
	'changes_have_been_saved'                   => esc_html__( 'Changes have been saved.', 'hivepress' ),
	'columns_number'                            => esc_html_x( 'Columns', 'quantity', 'hivepress' ),
	'items_number'                              => esc_html_x( 'Number', 'quantity', 'hivepress' ),
	'by_items_number'                           => esc_html_x( 'Item Count', 'sort order', 'hivepress' ),
	'by_name'                                   => esc_html_x( 'Name', 'sort order', 'hivepress' ),
	'by_date_added'                             => esc_html_x( 'Date Added', 'sort order', 'hivepress' ),
	'by_date_registered'                        => esc_html_x( 'Date Registered', 'sort order', 'hivepress' ),
	'by_title'                                  => esc_html_x( 'Title', 'sort order', 'hivepress' ),
	'by_random'                                 => esc_html_x( 'Random', 'sort order', 'hivepress' ),
	'sort_order'                                => esc_html_x( 'Order', 'sort', 'hivepress' ),
	'add_details_imperative'                    => esc_html_x( 'Add Details', 'imperative', 'hivepress' ),
	/* translators: %s: date. */
	'added_on_date'                             => esc_html__( 'Added on %s', 'hivepress' ),
	/* translators: %s: WooCommerce. */
	'ecommerce_product'                         => sprintf( esc_html__( '%s Product', 'hivepress' ), 'WooCommerce' ),
	/* translators: %s: token list. */
	'these_tokens_are_available'                => esc_html__( 'The following tokens are available: %s.', 'hivepress' ),
	/* translators: %s: extension name. */
	'extension_is_incompatible_with_core'       => esc_html__( '%s extension is incompatible with the current HivePress version, please update HivePress or deactivate it.', 'hivepress' ),
	/* translators: %s: terms URL. */
	'i_agree_to_terms_and_conditions'           => hp\sanitize_html( __( 'I agree to the <a href="%s" target="_blank">terms and conditions</a>', 'hivepress' ) ),
	/* translators: %s: field label. */
	'field_contains_invalid_value'              => esc_html__( '"%s" field contains an invalid value.', 'hivepress' ),
	/* translators: %s: field label. */
	'field_contains_too_many_values'            => esc_html__( '"%s" field contains too many values.', 'hivepress' ),

	// Listings.
	'listing'                                   => esc_html__( 'Listing', 'hivepress' ),
	'listings'                                  => esc_html__( 'Listings', 'hivepress' ),
	'all_listings'                              => esc_html__( 'All Listings', 'hivepress' ),
	'related_listings'                          => esc_html__( 'Related Listings', 'hivepress' ),
	/* translators: %s: Listings number. */
	'n_listings'                                => _n_noop( '%s Listing', '%s Listings', 'hivepress' ),
	/* translators: %s: Vendor name. */
	'listings_by_vendor'                        => esc_html__( 'Listings by %s', 'hivepress' ),
	'featuring_of_listings'                     => esc_html__( 'Featuring of Listings', 'hivepress' ),
	'featured_listings'                         => esc_html__( 'Featured Listings', 'hivepress' ),
	'view_listings'                             => esc_html__( 'View Listings', 'hivepress' ),
	'search_listings'                           => esc_html__( 'Search Listings', 'hivepress' ),
	'no_listings_found'                         => esc_html__( 'No listings found.', 'hivepress' ),
	'listing_attributes'                        => esc_html__( 'Listing Attributes', 'hivepress' ),
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
	/* translators: %s: Listing title. */
	'listing_has_been_submitted'                => esc_html__( 'Thank you! Your listing "%s" has been submitted and will be reviewed as soon as possible.', 'hivepress' ),
	/* translators: %s: Listing title. */
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
	'display_only_verified_listings'            => esc_html__( 'Display only verified listings', 'hivepress' ),
	'confirm_listing_deletion'                  => esc_html__( 'Are you sure you want to permanently delete this listing?', 'hivepress' ),
	'manually_approve_listings'                 => esc_html__( 'Manually approve new listings', 'hivepress' ),
	'allow_submitting_listings'                 => esc_html__( 'Allow submitting new listings', 'hivepress' ),
	'allow_reporting_listings'                  => esc_html__( 'Allow reporting listings', 'hivepress' ),
	'set_number_of_days_until_listing_expires'  => esc_html__( 'Set the number of days after which a listing expires.', 'hivepress' ),
	'set_number_of_days_until_listing_deleted'  => esc_html__( 'Set the number of days after which an expired listing is deleted.', 'hivepress' ),
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
	'vendor_attributes'                         => esc_html__( 'Vendor Attributes', 'hivepress' ),
	'vendor_search_form'                        => esc_html__( 'Vendor Search Form', 'hivepress' ),
	'vendors_page'                              => esc_html__( 'Vendors Page', 'hivepress' ),
	'regular_vendors_per_page'                  => esc_html__( 'Vendors per Page', 'hivepress' ),
	'choose_page_that_displays_all_vendors'     => esc_html__( 'Choose a page that displays all vendors.', 'hivepress' ),
	'display_vendors_on_frontend'               => esc_html__( 'Display vendors on the front-end', 'hivepress' ),
	'display_only_verified_vendors'             => esc_html__( 'Display only verified vendors', 'hivepress' ),
	'mark_vendor_as_verified'                   => esc_html__( 'Mark this vendor as verified', 'hivepress' ),

	// Categories.
	'category'                                  => esc_html__( 'Category', 'hivepress' ),
	'categories'                                => esc_html__( 'Categories', 'hivepress' ),
	'parent_category'                           => esc_html__( 'Parent Category', 'hivepress' ),

	// Attributes.
	'attributes'                                => esc_html__( 'Attributes', 'hivepress' ),
	'attribute'                                 => esc_html__( 'Attribute', 'hivepress' ),
	'add_new_attribute'                         => esc_html_x( 'Add New', 'attribute', 'hivepress' ),
	'add_attribute'                             => esc_html__( 'Add Attribute', 'hivepress' ),
	'edit_attribute'                            => esc_html__( 'Edit Attribute', 'hivepress' ),
	'search_attributes'                         => esc_html__( 'Search Attributes', 'hivepress' ),
	'no_attributes_found'                       => esc_html__( 'No attributes found.', 'hivepress' ),
];
