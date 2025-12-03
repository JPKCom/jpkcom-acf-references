<?php
/**
 * Asset Enqueuing - Scripts and Styles
 *
 * Handles enqueuing of JavaScript and CSS files for the plugin.
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue frontend scripts and styles
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'wp_enqueue_scripts', function(): void {

    // Enqueue reference styles CSS
    wp_enqueue_style(
        'jpkcom-acf-ref-styles',
        JPKCOM_ACFREFERENCES_PLUGIN_URL . 'assets/css/reference-styles.css',
        [], // No dependencies
        JPKCOM_ACFREFERENCES_VERSION,
        'all' // Media type
    );

    // Enqueue reference list filter JavaScript
    wp_enqueue_script(
        'jpkcom-acf-ref-list-filter',
        JPKCOM_ACFREFERENCES_PLUGIN_URL . 'assets/js/reference-list-filter.js',
        [], // No dependencies - vanilla JS
        JPKCOM_ACFREFERENCES_VERSION,
        true // Load in footer
    );

    // Enqueue gallery modal JavaScript on single reference pages
    if ( is_singular( 'reference' ) ) {
        wp_enqueue_script(
            'jpkcom-acf-ref-gallery-modal',
            JPKCOM_ACFREFERENCES_PLUGIN_URL . 'assets/js/gallery-modal.js',
            [], // No dependencies - vanilla JS, Bootstrap 5 is loaded by theme
            JPKCOM_ACFREFERENCES_VERSION,
            true // Load in footer
        );
    }

    // Enqueue minimal CSS for accessibility (visually-hidden class)
    wp_add_inline_style(
        'jpkcom-acf-ref-styles', // Add to our stylesheet
        '.filter-live-region.visually-hidden { position: absolute; width: 1px; height: 1px; padding: 0; margin: -1px; overflow: hidden; clip: rect(0,0,0,0); white-space: nowrap; border: 0; }'
    );

}, 20 );
