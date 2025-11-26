<?php
/**
 * Custom post type registration
 *
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'WPINC' ) ) {
    die;
}

add_action( 'init', function() {
	register_post_type( 'reference_customer', array(
	'labels' => array(
		'name' => 'Kunden',
		'singular_name' => 'Kunde',
		'menu_name' => 'Kunden',
		'all_items' => 'Alle Kunden',
		'edit_item' => 'Kunde bearbeiten',
		'view_item' => 'Kunde anzeigen',
		'view_items' => 'Kunden anzeigen',
		'add_new_item' => 'Neu hinzufügen: Kunde',
		'add_new' => 'Neu hinzufügen: Kunde',
		'new_item' => 'Neuer Inhaltstyp Kunde',
		'parent_item_colon' => 'Kunde, übergeordnet:',
		'search_items' => 'Kunden suchen',
		'not_found' => 'Kunden konnten nicht gefunden werden',
		'not_found_in_trash' => 'Kunden konnten nicht im Papierkorb gefunden werden',
		'archives' => 'Kunden-Archive',
		'attributes' => 'Kunden-Attribute',
		'insert_into_item' => 'In Kunde einfügen',
		'uploaded_to_this_item' => 'Zu diesem Kunden hochgeladen',
		'filter_items_list' => 'Kunden-Liste filtern',
		'filter_by_date' => 'Kunden nach Datum filtern',
		'items_list_navigation' => 'Kunden-Listen-Navigation',
		'items_list' => 'Kunden-Liste',
		'item_published' => 'Kunde wurde veröffentlicht.',
		'item_published_privately' => 'Kunde wurde privat veröffentlicht.',
		'item_reverted_to_draft' => 'Kunde wurde auf Entwurf zurückgesetzt.',
		'item_scheduled' => 'Kunde wurde geplant.',
		'item_updated' => 'Kunde wurde aktualisiert.',
		'item_link' => 'Kunden-Link',
		'item_link_description' => 'Ein Link zum Inhaltstyp Kunde',
	),
	'description' => 'Referenzkunde',
	'public' => false,
	'publicly_queryable' => true,
	'show_ui' => true,
	'show_in_menu' => 'edit.php?post_type=reference',
	'show_in_admin_bar' => false,
	'show_in_rest' => true,
	'menu_icon' => 'dashicons-admin-post',
	'supports' => array(
		0 => 'title',
		1 => 'revisions',
	),
	'delete_with_user' => false,
) );

	register_post_type( 'reference_location', array(
	'labels' => array(
		'name' => 'Orte',
		'singular_name' => 'Ort',
		'menu_name' => 'Orte',
		'all_items' => 'Alle Orte',
		'edit_item' => 'Ort bearbeiten',
		'view_item' => 'Ort anzeigen',
		'view_items' => 'Orte anzeigen',
		'add_new_item' => 'Neu hinzufügen: Ort',
		'add_new' => 'Neu hinzufügen: Ort',
		'new_item' => 'Neuer Inhaltstyp Ort',
		'parent_item_colon' => 'Ort, übergeordnet:',
		'search_items' => 'Orte suchen',
		'not_found' => 'Orte konnten nicht gefunden werden',
		'not_found_in_trash' => 'Orte konnten nicht im Papierkorb gefunden werden',
		'archives' => 'Orte-Archive',
		'attributes' => 'Ort-Attribute',
		'insert_into_item' => 'In Ort einfügen',
		'uploaded_to_this_item' => 'Zu diesem Ort hochgeladen',
		'filter_items_list' => 'Orte-Liste filtern',
		'filter_by_date' => 'Orte nach Datum filtern',
		'items_list_navigation' => 'Orte-Listen-Navigation',
		'items_list' => 'Orte-Liste',
		'item_published' => 'Ort wurde veröffentlicht.',
		'item_published_privately' => 'Ort wurde privat veröffentlicht.',
		'item_reverted_to_draft' => 'Ort wurde auf Entwurf zurückgesetzt.',
		'item_scheduled' => 'Ort wurde geplant.',
		'item_updated' => 'Ort wurde aktualisiert.',
		'item_link' => 'Orte-Link',
		'item_link_description' => 'Ein Link zu einem Inhaltstyp Ort',
	),
	'description' => 'Geben Sie hier Details zum Ort der Referenz an.',
	'public' => false,
	'publicly_queryable' => true,
	'show_ui' => true,
	'show_in_menu' => 'edit.php?post_type=reference',
	'show_in_admin_bar' => false,
	'show_in_rest' => true,
	'menu_icon' => 'dashicons-admin-post',
	'supports' => array(
		0 => 'title',
		1 => 'revisions',
	),
	'delete_with_user' => false,
) );

	register_post_type( 'reference', array(
	'labels' => array(
		'name' => 'Referenzen',
		'singular_name' => 'Referenz',
		'menu_name' => 'Referenzen',
		'all_items' => 'Alle Referenzen',
		'edit_item' => 'Referenz bearbeiten',
		'view_item' => 'Referenz anzeigen',
		'view_items' => 'Referenzen anzeigen',
		'add_new_item' => 'Neu hinzufügen: Referenz',
		'add_new' => 'Neu hinzufügen: Referenz',
		'new_item' => 'Neuer Inhaltstyp Referenz',
		'parent_item_colon' => 'Referenz, übergeordnet:',
		'search_items' => 'Referenz suchen',
		'not_found' => 'Referenz konnten nicht gefunden werden',
		'not_found_in_trash' => 'Referenz konnten nicht im Papierkorb gefunden werden',
		'archives' => 'Referenzen-Archiv',
		'attributes' => 'Referenz-Attribute',
		'featured_image' => 'Referenzbild',
		'set_featured_image' => 'Referenzbild festlegen',
		'remove_featured_image' => 'Referenzbild entfernen',
		'use_featured_image' => 'Als Referenzbild verwenden',
		'insert_into_item' => 'In Referenz einfügen',
		'uploaded_to_this_item' => 'Zu dieser Referenz hochgeladen',
		'filter_items_list' => 'Referenz-Liste filtern',
		'filter_by_date' => 'Referenzen nach Datum filtern',
		'items_list_navigation' => 'Referenzen-Listen-Navigation',
		'items_list' => 'Referenzen-Liste',
		'item_published' => 'Referenz wurde veröffentlicht.',
		'item_published_privately' => 'Referenz wurde privat veröffentlicht.',
		'item_reverted_to_draft' => 'Referenz wurde auf Entwurf zurückgesetzt.',
		'item_scheduled' => 'Referenz wurde geplant.',
		'item_updated' => 'Referenz wurde aktualisiert.',
		'item_link' => 'Referenz-Link',
		'item_link_description' => 'Ein Link zu dem Inhaltstyp Referenz ',
	),
	'description' => 'Referenz',
	'public' => true,
	'show_in_rest' => true,
	'rest_base' => 'references',
	'menu_icon' => 'dashicons-index-card',
	'supports' => array(
		0 => 'title',
		1 => 'author',
		2 => 'editor',
		3 => 'revisions',
		4 => 'thumbnail',
		5 => 'custom-fields',
	),
	'has_archive' => 'references',
	'delete_with_user' => false,
) );
} );


add_filter( 'enter_title_here', function( $default, $post ) {
	switch ( $post->post_type ) {
		case 'reference_customer':
			return 'Kundenname';
		case 'reference_location':
			return 'Name des Ortes';
	}

	return $default;
}, 10, 2 );
