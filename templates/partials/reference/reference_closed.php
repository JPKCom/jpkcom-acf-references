<?php
/**
 * Template Partial: reference_closed
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field ('reference_closed' ) ) { ?>

    <div class="alert alert-warning d-flex p-3" role="alert">
        <p><strong><?php echo __( 'Reference vacancy currently already filled!', 'jpkcom-acf-references' ); ?></strong></p>
    </div>

<?php } ?>
