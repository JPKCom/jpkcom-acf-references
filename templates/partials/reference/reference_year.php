<?php
/**
 * Template Partial: reference_year
 *
 * Displays the reference project year
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php
$year = get_field( 'reference_year' );
if ( $year ) :
?>

    <h3 class="fs-4"><?php echo esc_html__( 'Year', 'jpkcom-acf-references' ); ?></h3>
    <p class="mb-0"><?php echo esc_html( $year ); ?></p>

    <hr>

<?php endif; ?>
