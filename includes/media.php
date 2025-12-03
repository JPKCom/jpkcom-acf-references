<?php
/**
 * Media and image size registration functions
 *
 * Registers custom image sizes for reference posts, companies, and locations:
 * - jpkcom-acf-reference-16x9: 576x324px (16:9 aspect ratio) for reference images
 * - jpkcom-acf-reference-logo: 512x512px (square) for company logos
 * - jpkcom-acf-reference-header: 992x558px (16:9 aspect ratio) for header images
 * - jpkcom-acf-reference-card-overlay: 800x600px (4:3 aspect ratio) for image overlay cards
 * - jpkcom-acf-reference-gallery-thumb: 200x200px (square) for gallery thumbnails
 * - jpkcom-acf-reference-gallery-modal: 1400px width (proportional) for modal lightbox
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}


if ( ! function_exists( function: 'jpkcom_acf_references_media_size' ) ) {

    /**
     * Register custom image sizes for reference posts
     *
     * Registers six image sizes:
     * - jpkcom-acf-reference-16x9: 576x324px (16:9, hard crop)
     * - jpkcom-acf-reference-logo: 512x512px (square, hard crop)
     * - jpkcom-acf-reference-header: 992x558px (16:9, hard crop)
     * - jpkcom-acf-reference-card-overlay: 800x600px (4:3, hard crop)
     * - jpkcom-acf-reference-gallery-thumb: 200x200px (square, hard crop)
     * - jpkcom-acf-reference-gallery-modal: 1400px width (proportional, no crop)
     *
     * @since 1.0.0
     * @return void
     */
    function jpkcom_acf_references_media_size(): void {

        add_image_size( 'jpkcom-acf-reference-16x9', 576, 324, true );

        add_image_size( 'jpkcom-acf-reference-logo', 512, 512, true );

        add_image_size( 'jpkcom-acf-reference-header', 992, 558, true );

        add_image_size( 'jpkcom-acf-reference-card-overlay', 800, 600, true );

        add_image_size( 'jpkcom-acf-reference-gallery-thumb', 200, 200, true );

        add_image_size( 'jpkcom-acf-reference-gallery-modal', 1400, 0, false );

    }

}
add_action( 'after_setup_theme', 'jpkcom_acf_references_media_size' );


if ( ! function_exists( function: 'jpkcom_acf_references_image_sizes_to_selector' ) ) {

    /**
     * Add custom image sizes to media library size selector
     *
     * Makes custom image sizes available in the WordPress media library
     * dropdown when inserting images into posts.
     *
     * @since 1.0.0
     *
     * @param string[] $sizes Existing image size options.
     * @return string[] Modified array with custom sizes added.
     */
    function jpkcom_acf_references_image_sizes_to_selector( array $sizes ): array {

        return array_merge($sizes, [
            'jpkcom-acf-reference-16x9'   => __( 'Reference Image (16:9 / Width 576px)', 'jpkcom-acf-references' ),
            'jpkcom-acf-reference-logo' => __( 'Reference Logo (Width 512 / Height 512)', 'jpkcom-acf-references' ),
            'jpkcom-acf-reference-header' => __( 'Header Image (Width 992 / Height 558)', 'jpkcom-acf-references' ),
            'jpkcom-acf-reference-card-overlay' => __( 'Card Overlay Image (4:3 / Width 800 / Height 600)', 'jpkcom-acf-references' ),
            'jpkcom-acf-reference-gallery-thumb' => __( 'Gallery Thumbnail (Square / 200x200)', 'jpkcom-acf-references' ),
            'jpkcom-acf-reference-gallery-modal' => __( 'Gallery Modal Image (Width 1400px)', 'jpkcom-acf-references' ),
        ]);

    }

}
add_filter( 'image_size_names_choose', 'jpkcom_acf_references_image_sizes_to_selector' );
