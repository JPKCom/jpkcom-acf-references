<?php
/**
 * Shortcode template: list of references
 *
 * Available local variables (extracted by shortcode handler):
 * - array $posts  => array of WP_Post objects
 * - WP_Query $query
 * - array $atts   => raw shortcode attributes
 * - string $style
 * - string $class
 * - string $title
 * - bool $show_filters => Whether to display filter buttons
 * - array $filter_data => Filter configuration data
 * - bool $reset_button => Whether to display reset all filters button
 * - string $layout => Display layout ("list" or "cards")
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

if ( ! isset( $posts ) || ! is_array( value: $posts ) ) {

    echo '<p class="text-muted">' . esc_html__( 'No references to display.', 'jpkcom-acf-references' ) . '</p>';

    return;

}

// Generate unique ID for this list instance
$list_id = 'reference-filter-' . uniqid();
?>

<div class="jpkcom-acf-references--list<?php if ( ! empty( $class ) ) echo ' ' . esc_attr( $class ); ?>" id="<?php echo esc_attr( $list_id ); ?>" <?php if ( ! empty( $style ) ) echo 'style="' . esc_attr( $style ) . '"'; ?>>

    <?php if ( ! empty( $title ) ) : ?>
        <h3 class="mb-3"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <?php if ( $show_filters && ! empty( $filter_data ) ) : ?>
        <div class="jpkcom-acf-ref-filters mb-4" role="group" aria-label="<?php echo esc_attr__( 'Reference filters', 'jpkcom-acf-references' ); ?>">
            <div class="btn-group" role="group" aria-label="<?php echo esc_attr__( 'Filter button group', 'jpkcom-acf-references' ); ?>">
                <?php foreach ( $filter_data as $filter ) : ?>
                    <div class="btn-group" role="group">
                        <button
                            type="button"
                            class="btn btn-outline-primary dropdown-toggle jpkcom-acf-ref-filter-btn"
                            data-bs-toggle="dropdown"
                            aria-expanded="false"
                            aria-haspopup="true"
                            aria-label="<?php echo esc_attr( sprintf( __( 'Filter by %s', 'jpkcom-acf-references' ), $filter['label'] ) ); ?>"
                            data-filter-id="<?php echo esc_attr( $filter['id'] ); ?>"
                            data-filter-field="<?php echo esc_attr( $filter['field'] ); ?>"
                            data-default-label="<?php echo esc_attr( $filter['label'] ); ?>"
                        >
                            <span class="filter-label"><?php echo esc_html( $filter['label'] ); ?></span>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="<?php echo esc_attr( $filter['id'] ); ?>-dropdown">
                            <li>
                                <button
                                    class="dropdown-item jpkcom-acf-ref-filter-reset"
                                    type="button"
                                    data-filter-id="<?php echo esc_attr( $filter['id'] ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( __( 'Reset %s filter', 'jpkcom-acf-references' ), $filter['label'] ) ); ?>"
                                >
                                    <strong><?php echo esc_html__( 'Show all', 'jpkcom-acf-references' ); ?></strong>
                                </button>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <?php foreach ( $filter['terms'] as $term ) : ?>
                                <li>
                                    <button
                                        class="dropdown-item jpkcom-acf-ref-filter-option"
                                        type="button"
                                        data-filter-id="<?php echo esc_attr( $filter['id'] ); ?>"
                                        data-term-id="<?php echo esc_attr( $term->term_id ); ?>"
                                        data-term-name="<?php echo esc_attr( $term->name ); ?>"
                                        aria-label="<?php echo esc_attr( sprintf( __( 'Filter by %s', 'jpkcom-acf-references' ), $term->name ) ); ?>"
                                    >
                                        <?php echo esc_html( $term->name ); ?>
                                    </button>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ( $reset_button ) : ?>
                <button
                    type="button"
                    class="btn btn-secondary ms-2 jpkcom-acf-ref-reset-all"
                    aria-label="<?php echo esc_attr__( 'Reset all filters', 'jpkcom-acf-references' ); ?>"
                >
                    <?php echo esc_html__( 'Reset all', 'jpkcom-acf-references' ); ?>
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ( $show_filters && ! empty( $filter_data ) ) : ?>
        <div class="alert alert-secondary jpkcom-acf-ref-no-results" role="alert" style="display: none;" aria-live="polite">
            <?php echo esc_html__( 'No references found matching your filter criteria. Please adjust your filters.', 'jpkcom-acf-references' ); ?>
        </div>
    <?php endif; ?>

    <?php
    // Load the appropriate layout partial
    $partial_name = $layout === 'cards' ? 'list-cards.php' : 'list-items.php';
    $partial_path = jpkcom_acf_references_locate_template( 'shortcodes/partials/' . $partial_name );

    if ( $partial_path ) {
        include $partial_path;
    } else {
        // Fallback if partial not found
        echo '<p class="text-danger">' . esc_html__( 'Layout template not found.', 'jpkcom-acf-references' ) . '</p>';
    }
    ?>

</div>
