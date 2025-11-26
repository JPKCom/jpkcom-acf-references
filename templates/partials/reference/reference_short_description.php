<?php
/**
 * Template Partial: reference_short_description
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<p class="lead">
    <?php echo wp_kses_post( get_field( 'reference_short_description' ) ); ?>
</p>
