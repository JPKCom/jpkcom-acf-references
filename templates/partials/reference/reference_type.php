<?php
/**
 * Template Partial: reference_type
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_type' ) ) { ?>

    <h3 class="fs-4"><?php echo __( 'Type', 'jpkcom-acf-references' ); ?></h3>
    <?php

    $types = get_field( 'reference_type' );

    if ( $types && is_array( value: $types ) ) {

        $total = count( value: $types );
        $i = 0;

        echo '<p class="fs-5">';

        foreach ( $types as $type ) {

            // Handle both array format and string format for backwards compatibility
            if ( is_array( value: $type ) && isset( $type['label'] ) ) {
                echo $type['label'];
            } elseif ( is_string( value: $type ) ) {
                // Fallback: use the value itself if it's a string
                echo $type;
            }
            $i++;

            if ( $i < $total ) {
                echo ', ';

            }

        }

        echo '</p>';

    }
    ?>

    <?php if ( get_field( 'reference_work_type' ) ) {

        $reference_work_type = get_field( 'reference_work_type' );
        $reference_work_type_label = '';

        // Handle both array format and string format for backwards compatibility
        $work_type_value = is_array( value: $reference_work_type ) && isset( $reference_work_type['value'] ) ? $reference_work_type['value'] : $reference_work_type;

        if ( $work_type_value === 'homeoffice' ) {

            $reference_work_type_label = __( 'Home office', 'jpkcom-acf-references' );

        } elseif ( $work_type_value === 'onsitework' ) {

            $reference_work_type_label = __( 'Onsite work', 'jpkcom-acf-references' );

        } else {

            $reference_work_type_label = __( 'Home office and onsite work', 'jpkcom-acf-references' );

        }

        echo '<p>' . __( 'Ways of working', 'jpkcom-acf-references' ) . ': ';
        echo $reference_work_type_label;
        echo '</p>';

    } ?>

    <hr>

<?php } ?>
