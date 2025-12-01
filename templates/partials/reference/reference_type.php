<?php
/**
 * Template Partial: reference_attribute
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_type' ) ) {

    $reference_types = get_field( 'reference_type' );

    if ( $reference_types ) {

        echo '<h3 class="fs-4">' . __( 'Type', 'jpkcom-acf-references' ) . '</h3>';

        echo '<dl>';

        foreach ( $reference_types as $attr ) {


            if ( is_numeric( value: $attr ) ) {

                $term = get_term( $attr );

            } elseif ( is_object( value: $attr ) && $attr instanceof WP_Term ) {

                $term = $attr;

            } elseif ( is_string( value: $attr ) ) {

                $term = get_term_by( 'name', $attr, 'reference_type' );

            } else {

                continue;

            }

            if ( ! $term || is_wp_error( $term ) ) {

                continue;

            }

            echo '<dt>' . esc_html( $term->name ) . '</dt>';
            
            if ( ! empty( $term->description ) ) {

                echo '<dd>' . nl2br( string: wp_kses_post( $term->description ) ) . '</dd>';

            }

        }

        echo '</dl>';

        echo '<hr>';

    }

} ?>
