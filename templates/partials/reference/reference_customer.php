<?php
/**
 * Template Partial: reference_customer
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_customer' ) ) { ?>

    <h3 class="fs-4"><?php echo __( 'Customer', 'jpkcom-acf-references' ); ?></h3>
    <?php

    $customers = get_field( 'reference_customer' );

    if ( $customers && is_array( value: $customers ) ) {
        
        $total = count( value: $customers );
        $i = 0;

        foreach ( $customers as $customer ) {

            $reference_customer_url_HTML_Before = '';
            $reference_customer_url_HTML_After = '';
            $reference_customer_url_HTML_Closing = '';
            $reference_customer_url = '';

            if ( get_field( 'reference_customer_url', $customer->ID ) ) {

                $reference_customer_url_HTML_Before = '<a class="link-light link-offset-2 link-underline-opacity-25 link-underline-opacity-100-hover" href="';
                $reference_customer_url_HTML_After = '">';
                $reference_customer_url_HTML_Closing = '</a>';
                $reference_customer_url_array = get_field( 'reference_customer_url', $customer->ID );
                $reference_customer_url = esc_url( $reference_customer_url_array['url'] );

            }

            echo '<div class="row mb-3">';
            echo '<div class="col-2">';

            if ( get_field( 'reference_customer_logo', $customer->ID ) ) {

                $reference_customer_logo = get_field( 'reference_customer_logo', $customer->ID );
                $size = 'jpkcom-acf-reference-logo';

                echo $reference_customer_url_HTML_Before . $reference_customer_url . $reference_customer_url_HTML_After;
                echo wp_get_attachment_image( $reference_customer_logo, $size );

                    if ( is_array( value: $reference_customer_logo ) && isset( $reference_customer_logo['ID'] ) ) {

                        echo wp_get_attachment_image( $reference_customer_logo['ID'], $size, false, [
                            'class' => 'img-fluid rounded shadow-sm',
                            'alt'   => esc_attr( $reference_customer_logo['alt'] ?? get_the_title( $customer->ID ) ),
                        ] );

                    } elseif ( is_numeric( value: $reference_customer_logo ) ) {

                        echo wp_get_attachment_image( $reference_customer_logo, $size, false, [
                            'class' => 'img-fluid rounded shadow-sm',
                        ] );

                    }

                echo $reference_customer_url_HTML_Closing;

            }

            echo '</div>';
            echo '<div class="col-10">';
            echo '<p class="fs-5"><strong>' . $reference_customer_url_HTML_Before . $reference_customer_url . $reference_customer_url_HTML_After . get_the_title( $customer->ID ) . $reference_customer_url_HTML_Closing . '</strong></p>';
            echo '</div>';
            echo '</div>';

        }

    }
    ?>

    <hr>

<?php } ?>
