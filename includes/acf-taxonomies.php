<?php
/**
 * Custom Taxonomy Registration
 *
 * Registers three hierarchical taxonomies for categorizing and filtering references:
 * - reference-type: Main categorization for references (e.g., Web Development, Design)
 * - reference-filter-1: First custom filter dimension (project-specific)
 * - reference-filter-2: Second custom filter dimension (project-specific)
 *
 * All taxonomies support hierarchical organization and are available for filtering
 * in both admin and frontend contexts.
 *
 * @package JPKCom_ACF_References
 * @since   1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'WPINC' ) ) {
    die;
}

/**
 * Register custom taxonomies for reference filtering and categorization.
 *
 * Hooks into WordPress 'init' action to register three hierarchical taxonomies:
 * - reference-filter-1: First custom filter dimension (configurable)
 * - reference-filter-2: Second custom filter dimension (configurable)
 * - reference-type: Main reference categorization taxonomy
 *
 * All taxonomies are hidden from public display but available in admin and REST API.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', function() {
	register_taxonomy( 'reference-filter-1', array(
	0 => 'reference',
), array(
	'labels' => array(
		'name' => 'Referenzfilter 1',
		'singular_name' => 'Referenzfilter 1',
		'menu_name' => 'Referenzfilter 1',
		'all_items' => 'Alle Referenzfilter 1 Elemente',
		'edit_item' => 'Referenzfilter 1 bearbeiten',
		'view_item' => 'Referenzfilter 1 anzeigen',
		'update_item' => 'Referenzfilter 1 aktualisieren',
		'add_new_item' => 'Neu hinzufügen: Referenzfilter 1',
		'new_item_name' => 'Neuer Referenzfilter 1 Name',
		'search_items' => 'Referenzfilter 1 suchen',
		'popular_items' => 'Beliebte Referenzfilter 1',
		'separate_items_with_commas' => 'Trenne Referenzfilter 1 durch Kommas',
		'add_or_remove_items' => 'Referenzfilter 1 hinzufügen oder entfernen',
		'choose_from_most_used' => 'Wähle aus den meistgenutzten Referenzfilter 1',
		'not_found' => 'Referenzfilter 1 konnten nicht gefunden werden',
		'no_terms' => 'Keine Referenzfilter 1 Taxonomien',
		'items_list_navigation' => 'Referenzfilter 1 Listen-Navigation',
		'items_list' => 'Referenzfilter 1 Liste',
		'back_to_items' => '← Zu Referenzfilter 1 gehen',
		'item_link' => 'Referenzfilter 1 Link',
		'item_link_description' => 'Ein Link zu einer Taxonomie des Referenzfilter 1',
	),
	'description' => 'Referenzfilter 1 Einstellungen.',
	'public' => false,
	'show_ui' => true,
	'show_in_menu' => true,
	'show_in_rest' => true,
	'show_tagcloud' => false,
	'show_admin_column' => true,
	'meta_box_cb' => false,
	'rewrite' => array(
		'with_front' => false,
	),
	'sort' => true,
) );

	register_taxonomy( 'reference-filter-2', array(
	0 => 'reference',
), array(
	'labels' => array(
		'name' => 'Referenzfilter 2',
		'singular_name' => 'Referenzfilter 2',
		'menu_name' => 'Referenzfilter 2',
		'all_items' => 'Alle Referenzfilter 2 Elemente',
		'edit_item' => 'Referenzfilter 2 bearbeiten',
		'view_item' => 'Referenzfilter 2 anzeigen',
		'update_item' => 'Referenzfilter 2 aktualisieren',
		'add_new_item' => 'Neu hinzufügen: Referenzfilter 2',
		'new_item_name' => 'Neuer Referenzfilter 2 Name',
		'search_items' => 'Referenzfilter 2 suchen',
		'popular_items' => 'Beliebte Referenzfilter 2',
		'separate_items_with_commas' => 'Trenne Referenzfilter 2 durch Kommas',
		'add_or_remove_items' => 'Referenzfilter 2 hinzufügen oder entfernen',
		'choose_from_most_used' => 'Wähle aus den meistgenutzten Referenzfilter 2',
		'not_found' => 'Referenzfilter 2 konnten nicht gefunden werden',
		'no_terms' => 'Keine Referenzfilter 2 Taxonomien',
		'items_list_navigation' => 'Referenzfilter 2 Listen-Navigation',
		'items_list' => 'Referenzfilter 2 Liste',
		'back_to_items' => '← Zu Referenzfilter 2 gehen',
		'item_link' => 'Referenzfilter 2 Link',
		'item_link_description' => 'Ein Link zu einer Taxonomie des Referenzfilter 2',
	),
	'description' => 'Referenzfilter 2 Einstellungen.',
	'public' => false,
	'show_ui' => true,
	'show_in_menu' => true,
	'show_in_rest' => true,
	'show_tagcloud' => false,
	'show_admin_column' => true,
	'meta_box_cb' => false,
	'rewrite' => array(
		'with_front' => false,
	),
	'sort' => true,
) );

	register_taxonomy( 'reference-type', array(
	0 => 'reference',
), array(
	'labels' => array(
		'name' => 'Referenztypen',
		'singular_name' => 'Referenztyp',
		'menu_name' => 'Referenztypen',
		'all_items' => 'Alle Referenztypen',
		'edit_item' => 'Referenztyp bearbeiten',
		'view_item' => 'Referenztyp anzeigen',
		'update_item' => 'Referenztyp aktualisieren',
		'add_new_item' => 'Neu hinzufügen: Referenztyp',
		'new_item_name' => 'Neuer Referenztyp-Name',
		'search_items' => 'Referenztypen suchen',
		'popular_items' => 'Beliebte Referenztypen',
		'separate_items_with_commas' => 'Trenne Referenztypen durch Kommas',
		'add_or_remove_items' => 'Referenztypen hinzufügen oder entfernen',
		'choose_from_most_used' => 'Wähle aus den meistgenutzten Referenztypen',
		'not_found' => 'Referenztypen konnten nicht gefunden werden',
		'no_terms' => 'Keine Referenztypen-Taxonomien',
		'items_list_navigation' => 'Referenztypen-Listen-Navigation',
		'items_list' => 'Referenztypen-Liste',
		'back_to_items' => '← Zu Referenztypen gehen',
		'item_link' => 'Referenztyp-Link',
		'item_link_description' => 'Ein Link zu der Taxonomie Referenztypen',
	),
	'description' => 'Referenztypen, Objekttypen etc.',
	'public' => false,
	'show_ui' => true,
	'show_in_menu' => true,
	'show_in_rest' => true,
	'show_tagcloud' => false,
	'show_admin_column' => true,
	'meta_box_cb' => false,
	'rewrite' => array(
		'with_front' => false,
	),
	'sort' => true,
) );
} );
