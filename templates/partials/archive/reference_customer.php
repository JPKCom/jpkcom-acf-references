<?php
/**
 * Template Partial: reference_customer
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_customer' ) ) { ?>
    <li class="d-block">
        <strong><?php echo __( 'Customer', 'jpkcom-acf-references' ); ?>:</strong><br>
        <?php
        $customers = get_field( 'reference_customer' );
        if ( $customers && is_array( value: $customers ) ) {
            $total = count( value: $customers );
            $i = 0;
            foreach ( $customers as $customer ) {
                echo get_the_title( $customer->ID );
                $i++;
                if ( $i < $total ) {
                    echo ',<br>';
                }
            }
        }
        ?>
    </li>
<?php } ?>
