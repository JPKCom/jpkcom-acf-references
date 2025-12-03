<?php
/**
 * Redirect and access control functions
 *
 * Handles three types of redirects:
 * 1. Reference URL redirects (external reference URLs)
 * 2. Location/Customer access control (non-editors can't view directly)
 * 3. Expired reference redirects (redirect to archive if reference expired)
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}


/**
 * Redirect reference posts to external URL if configured
 *
 * Checks for reference_url ACF field and redirects to external reference URL.
 * Skips redirect for administrators and when WP_DEBUG is enabled.
 * Uses 307 (Temporary Redirect) to preserve the request method.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', function(): void {

    if ( is_admin() ) {

        return;

    }

    if ( is_singular( 'reference' ) ) {

        global $post;

        if ( ! $post ) {

            return;

        }

        if ( current_user_can( 'administrator' ) ) {

            return;

        }

        $reference_url = get_field( 'reference_url', $post->ID );

        if ( is_array( value: $reference_url ) && ! empty( $reference_url['url'] ) ) {

            $redirect_url = $reference_url['url'];

            if ( strpos( haystack: $redirect_url, needle: home_url() ) === false && strpos( haystack: $redirect_url, needle: '://' ) === false ) {

                $redirect_url = home_url( $redirect_url );

            }

            if ( defined( constant_name: 'WP_DEBUG' ) && WP_DEBUG ) {

                return;

            }

            wp_redirect( $redirect_url, 307 );

            exit;

        }

    }

});


/**
 * Restrict direct access to reference_location and reference_customer posts
 *
 * Redirects non-editors from directly viewing location/customer posts
 * to the main reference archive. Uses 302 (Found) status code.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', function(): void {

    if ( is_singular( [ 'reference_location', 'reference_customer' ] ) ) {

        global $post;

        if ( ! $post ) {

            return;

        }

        if ( ! current_user_can( 'edit_post', $post->ID ) ) {

            wp_safe_redirect( get_post_type_archive_link( 'reference' ), 302 );

            exit;

        }

    }

});


/**
 * Redirect expired references to archive
 *
 * Checks reference_expiry_date ACF field and redirects to reference archive if expired.
 * Editors can still view expired references. Uses 307 (Temporary Redirect) status.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', function(): void {

    if ( is_singular( 'reference' ) ) {

        global $post;

        if ( ! $post ) {

            return;

        }

        if ( current_user_can( 'edit_post', $post->ID ) ) {

            return;

        }

        $expiry_date = get_field( 'reference_expiry_date', $post->ID );

        if ( empty( $expiry_date ) ) {

            return;

        }

        $today = date( format: 'Y-m-d' );
        $is_expired = ( $expiry_date < $today );

        if ( $is_expired ) {

            wp_safe_redirect( get_post_type_archive_link( 'reference' ), 307 );

            exit;

        }

    }

});


/**
 * Redirect reference archive if disabled
 *
 * Redirects all access to the reference archive page when disabled in plugin options.
 * Redirects to custom URL or homepage. Uses 307 (Temporary Redirect) status.
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'template_redirect', function(): void {

    // Only proceed if we're on the reference archive page
    if ( ! is_post_type_archive( 'reference' ) ) {
        return;
    }

    // Check if archive is disabled
    $disable_archive = get_option( 'jpkcom_acf_ref_disable_archive', false );
    if ( ! $disable_archive ) {
        return;
    }

    // Get custom redirect URL or use homepage
    $redirect_url = get_option( 'jpkcom_acf_ref_archive_redirect_url', '' );
    if ( empty( $redirect_url ) ) {
        $redirect_url = home_url( '/' );
    }

    // Validate URL before redirecting
    $redirect_url = esc_url_raw( $redirect_url );
    if ( ! empty( $redirect_url ) ) {
        wp_redirect( $redirect_url, 307 );
        exit;
    }

}, 10 );
