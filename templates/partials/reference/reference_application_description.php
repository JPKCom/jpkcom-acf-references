<?php
/**
 * Template Partial: reference_application_description
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_application_show_description' ) ) { ?>
    <?php if ( get_field( 'reference_application_description' ) ) { ?>

        <hr>

        <?php echo wp_kses_post( get_field( 'reference_application_description' ) ); ?>

    <?php } ?>
<?php } ?>
