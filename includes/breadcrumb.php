<?php
/**
 * Breadcrumb navigation functions
 *
 * Generates Bootstrap 5 styled breadcrumb navigation for reference posts
 * and archives with proper semantic markup and accessibility.
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}


if ( ! function_exists( function: 'jpkcom_acf_references_breadcrumb' ) ) {

    /**
     * Output Bootstrap 5 breadcrumb navigation
     *
     * Generates breadcrumb navigation for:
     * - Single reference posts (Home > References > Reference Title)
     * - Reference archive (Home > References)
     * - Other pages (Home > Page Title)
     *
     * Includes proper ARIA labels and semantic HTML5 markup.
     *
     * @since 1.0.0
     *
     * @global WP_Post $post Current post object.
     * @return void Outputs HTML directly.
     */
    function jpkcom_acf_references_breadcrumb(): void {

        global $post;

        echo '<nav aria-label="' . esc_html__( 'Breadcrumb', 'jpkcom-acf-references' ) . '" class="overflow-x-auto text-nowrap mb-4 mt-2 py-2 px-3 bg-body-tertiary rounded">';
        echo '<ol class="breadcrumb flex-nowrap mb-0">';

        echo '<li class="breadcrumb-item"><a href="' . esc_url( home_url( '/' ) ) . '"><i class="fa-solid fa-house"></i><span class="visually-hidden">' . esc_html__( 'Home', 'jpkcom-acf-references' ) . '</span></a></li>';

        if ( is_singular( 'reference' ) ) {

            $archive_link = get_post_type_archive_link( 'reference' );

            if ( $archive_link ) {

                echo '<li class="breadcrumb-item"><a href="' . esc_url( $archive_link ) . '">' . esc_html__( 'References', 'jpkcom-acf-references' ) . '</a></li>';

            }

            echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html( get_the_title() ) . '</li>';

        } elseif ( is_post_type_archive( 'reference' ) ) {

            echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html__( 'References', 'jpkcom-acf-references' ) . '</li>';

        } else {

            echo '<li class="breadcrumb-item active" aria-current="page">' . esc_html( get_the_title() ) . '</li>';

        }

        echo '</ol>';
        echo '</nav>';

    }

}
