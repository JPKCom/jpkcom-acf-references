<?php
/**
 * Shortcode template: list of reference filter 2 (taxonomy terms)
 *
 * Local variables:
 * - array|WP_Term[] $terms
 * - array $atts
 * - string $style
 * - string $class
 * - string $title
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

if ( ! isset( $terms ) || ! is_array( value: $terms ) ) {

    echo '<p class="text-muted">' . esc_html__( 'No filter terms found.', 'jpkcom-acf-references' ) . '</p>';

    return;

}
?>

<div class="jpkcom-acf-references--filter-2<?php if ( ! empty( $class ) ) { echo ' ' . esc_attr( $class ); } ?>" <?php if ( ! empty( $style ) ) { echo 'style="' . esc_attr( $style ) . '"'; } ?>>

    <?php if ( ! empty( $title ) ) : ?>
        <h3 class="mb-3"><?php echo esc_html( $title ); ?></h3>
    <?php endif; ?>

    <?php foreach ( $terms as $term ) :
        $summary = esc_html( $term->name );
        $desc = wp_kses_post( term_description( $term->term_id, $term->taxonomy ) );
    ?>
        <details name="jpkcom-acf-references-filter-2" class="border rounded p-3 mb-3">
            <summary class="px-3 fs-5"><h4 class="d-inline fs-5"><?php echo $summary; ?></h4></summary>
            <div class="p-3"><?php echo $desc ?: '<p class="text-muted">' . esc_html__( 'No description.', 'jpkcom-acf-references' ) . '</p>'; ?></div>
        </details>
    <?php endforeach; ?>

</div>
