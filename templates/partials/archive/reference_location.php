<?php
/**
 * Template Partial: reference_location
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_location' ) ) { ?>
    <li class="d-block">
        <strong><?php echo __( 'Location', 'jpkcom-acf-references' ); ?>:</strong><br>
        <?php
        $locations = get_field( 'reference_location' );
        if ( $locations && is_array( value: $locations ) ) {
            $total = count( value: $locations );
            $i = 0;
            foreach ( $locations as $location ) {
                echo get_the_title( $location->ID );
                $i++;
                if ( $i < $total ) {
                    echo ',<br>';
                }
            }
        }
        ?>
    </li>
<?php } ?>
