<?php
/*
Plugin Name: JPKCom ACF References
Plugin URI: https://github.com/JPKCom/jpkcom-acf-references
Description: Reference gallery with filter function plugin for ACF
Version: 1.0.1
Author: Jean Pierre Kolb <jpk@jpkc.com>
Author URI: https://www.jpkc.com/
Contributors: JPKCom
Tags: ACF, Fields, CPT, CTT, Taxonomy, Images
Requires Plugins: advanced-custom-fields-pro, acf-quickedit-fields
Requires at least: 6.8
Tested up to: 6.9
Requires PHP: 8.3
Network: true
Stable tag: 1.0.1
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: jpkcom-acf-references
Domain Path: /languages
*/

declare(strict_types=1);

if ( ! defined( constant_name: 'WPINC' ) ) {
    die;
}

/**
 * Plugin Constants
 *
 * @since 1.0.0
 */
if ( ! defined( 'JPKCOM_ACFREFERENCES_VERSION' ) ) {
	define( 'JPKCOM_ACFREFERENCES_VERSION', '1.0.1' );
}

if ( ! defined( 'JPKCOM_ACFREFERENCES_BASENAME' ) ) {
	define( 'JPKCOM_ACFREFERENCES_BASENAME', plugin_basename( __FILE__ ) );
}

if ( ! defined( 'JPKCOM_ACFREFERENCES_PLUGIN_PATH' ) ) {
	define( 'JPKCOM_ACFREFERENCES_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'JPKCOM_ACFREFERENCES_PLUGIN_URL' ) ) {
	define( 'JPKCOM_ACFREFERENCES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}


/**
 * Initialize Plugin Updater
 *
 * Loads and initializes the GitHub-based plugin updater with SHA256 checksum verification.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', static function (): void {
	$updater_file = JPKCOM_ACFREFERENCES_PLUGIN_PATH . 'includes/class-plugin-updater.php';

	if ( file_exists( $updater_file ) ) {
		require_once $updater_file;

		if ( class_exists( 'JPKComAcfReferencesGitUpdate\\JPKComGitPluginUpdater' ) ) {
			new \JPKComAcfReferencesGitUpdate\JPKComGitPluginUpdater(
				plugin_file: __FILE__,
				current_version: JPKCOM_ACFREFERENCES_VERSION,
				manifest_url: 'https://jpkcom.github.io/jpkcom-acf-references/plugin_jpkcom-acf-references.json'
			);
		}
	}
}, 5 );


/**
 * Load plugin text domain for translations
 *
 * Loads translation files from the /languages directory.
 *
 * @since 1.0.0
 * @return void
 */
function jpkcom_acfreferences_textdomain(): void {
    load_plugin_textdomain(
        'jpkcom-acf-references',
        false,
        dirname( path: JPKCOM_ACFREFERENCES_BASENAME ) . '/languages'
    );
}

add_action( 'plugins_loaded', 'jpkcom_acfreferences_textdomain' );


/**
 * Locate file with override support
 *
 * Searches for a file in multiple locations with priority:
 * 1. Child theme
 * 2. Parent theme
 * 3. MU plugin overrides
 * 4. Plugin includes directory
 *
 * @since 1.0.0
 *
 * @param string $filename The filename to locate (without path).
 * @return string|null Full path to the file if found, null otherwise.
 */
function jpkcom_acfreferences_locate_file( string $filename ): ?string {

    $paths = [
        get_stylesheet_directory() . '/jpkcom-acf-references/' . $filename,
        get_template_directory() . '/jpkcom-acf-references/' . $filename,
        WPMU_PLUGIN_DIR . '/jpkcom-acf-references-overrides/' . $filename,
        JPKCOM_ACFREFERENCES_PLUGIN_PATH . 'includes/' . $filename,
    ];

    /**
     * Filter the file search paths
     *
     * @since 1.0.0
     *
     * @param string[] $paths    Array of paths to search.
     * @param string   $filename The filename being located.
     */
    $paths = apply_filters( 'jpkcom_acfreferences_file_paths', $paths, $filename );

    foreach ( $paths as $path ) {

        if ( file_exists( filename: $path ) ) {

            return $path;

        }

    }

    return null;

}


/**
 * Load media functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceMedia = jpkcom_acfreferences_locate_file( filename: 'media.php' );

if ( $jpkcomAcfReferenceMedia ) {

    require_once $jpkcomAcfReferenceMedia;

}


/**
 * Load archive functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceArchive = jpkcom_acfreferences_locate_file( filename: 'archive.php' );

if ( $jpkcomAcfReferenceArchive ) {

    require_once $jpkcomAcfReferenceArchive;

}


/**
 * Load breadcrumb functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceBreadcrumb = jpkcom_acfreferences_locate_file( filename: 'breadcrumb.php' );

if ( $jpkcomAcfReferenceBreadcrumb ) {

    require_once $jpkcomAcfReferenceBreadcrumb;

}


/**
 * Load pagination functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferencePagination = jpkcom_acfreferences_locate_file( filename: 'pagination.php' );

if ( $jpkcomAcfReferencePagination ) {

    require_once $jpkcomAcfReferencePagination;

}


/**
 * Load redirect functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceRedirect = jpkcom_acfreferences_locate_file( filename: 'redirects.php' );

if ( $jpkcomAcfReferenceRedirect ) {

    require_once $jpkcomAcfReferenceRedirect;

}


/**
 * Load helper functions
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceHelpers = jpkcom_acfreferences_locate_file( filename: 'helpers.php' );

if ( $jpkcomAcfReferenceHelpers ) {

    require_once $jpkcomAcfReferenceHelpers;

}


/**
 * Register Custom Post Types & Taxonomies
 *
 * Loads and registers reference, reference_location, reference_customer post types
 * and reference-type, reference-filter-1, reference-filter-2 taxonomies.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', function(): void {

    $post_types_file = jpkcom_acfreferences_locate_file( filename: 'acf-post_types.php' );

    if ( $post_types_file ) {

        require_once $post_types_file;

    }

    $taxonomies_file = jpkcom_acfreferences_locate_file( filename: 'acf-taxonomies.php' );

    if ( $taxonomies_file ) {

        require_once $taxonomies_file;

    }

}, 5 );


/**
 * Register ACF Field Groups
 *
 * Loads programmatically registered ACF field groups for references,
 * locations, and customers.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'plugins_loaded', function(): void {

    $field_groups_file = jpkcom_acfreferences_locate_file( filename: 'acf-field_groups.php' );

    if ( $field_groups_file ) {

        require_once $field_groups_file;

    }

});


/**
 * Load template loader
 *
 * Handles template hierarchy and override system for reference templates.
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceTemplateLoader = jpkcom_acfreferences_locate_file( filename: 'template-loader.php' );

if ( $jpkcomAcfReferenceTemplateLoader ) {

    require_once $jpkcomAcfReferenceTemplateLoader;

}


/**
 * Load WPML + ACF field keys fix
 *
 * Ensures ACF field keys are copied to WPML translations for proper field formatting.
 *
 * @since 1.0.1
 */
$jpkcomAcfReferenceWpmlFix = jpkcom_acfreferences_locate_file( filename: 'wpml-acf-field-keys-fix.php' );

if ( $jpkcomAcfReferenceWpmlFix ) {

    require_once $jpkcomAcfReferenceWpmlFix;

}


/**
 * Load shortcode functions
 *
 * Registers [jpkcom_acf_references_list] and [jpkcom_acf_references_types] shortcodes.
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceShortcodes = jpkcom_acfreferences_locate_file( filename: 'shortcodes.php' );

if ( $jpkcomAcfReferenceShortcodes ) {

    require_once $jpkcomAcfReferenceShortcodes;

}


/**
 * Load asset enqueuing functions
 *
 * Handles enqueuing of JavaScript and CSS files.
 *
 * @since 1.0.0
 */
$jpkcomAcfReferenceAssets = jpkcom_acfreferences_locate_file( filename: 'assets-enqueue.php' );

if ( $jpkcomAcfReferenceAssets ) {

    require_once $jpkcomAcfReferenceAssets;

}


/**
 * Load admin pages and settings
 *
 * Registers admin pages for shortcode generator and plugin options.
 *
 * @since 1.0.0
 */
if ( is_admin() ) {

    $jpkcomAcfReferenceAdminPages = jpkcom_acfreferences_locate_file( filename: 'admin-pages.php' );

    if ( $jpkcomAcfReferenceAdminPages ) {

        require_once $jpkcomAcfReferenceAdminPages;

    }

}
