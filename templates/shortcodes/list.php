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
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

if ( ! isset( $posts ) || ! is_array( value: $posts ) ) {

    echo '<p class="text-muted">' . esc_html__( 'No references to display.', 'jpkcom-acf-references' ) . '</p>';

    return;

}

// Generate unique ID for this list instance
$list_id = 'jpkcom-acf-ref-list-' . uniqid();
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
        </div>
    <?php endif; ?>

    <ul class="list-unstyled jpkcom-acf-ref-items">
        <?php foreach ( $posts as $post_item)  : setup_postdata( $post_item ); ?>
            <?php
            // Collect all taxonomy term IDs for this post for filtering
            $filter_attributes = [];

            // Reference Type
            $ref_types = get_field( 'reference_type', $post_item->ID );
            $ref_type_ids = [];
            if ( $ref_types && is_array( value: $ref_types ) ) {
                foreach ( $ref_types as $type ) {
                    $term_id = null;
                    if ( is_numeric( value: $type ) ) {
                        $term_id = intval( $type );
                    } elseif ( is_object( value: $type ) && $type instanceof WP_Term ) {
                        $term_id = $type->term_id;
                    } elseif ( is_string( value: $type ) ) {
                        $term_obj = get_term_by( 'name', $type, 'reference-type' );
                        if ( $term_obj && ! is_wp_error( $term_obj ) ) {
                            $term_id = $term_obj->term_id;
                        }
                    }
                    if ( $term_id ) {
                        $ref_type_ids[] = $term_id;
                    }
                }
            }
            $filter_attributes['data-reference-type'] = implode( ',', $ref_type_ids );

            // Reference Filter 1
            $ref_filter_1 = get_field( 'reference_filter_1', $post_item->ID );
            $ref_filter_1_ids = [];
            if ( $ref_filter_1 && is_array( value: $ref_filter_1 ) ) {
                foreach ( $ref_filter_1 as $f1 ) {
                    $term_id = null;
                    if ( is_numeric( value: $f1 ) ) {
                        $term_id = intval( $f1 );
                    } elseif ( is_object( value: $f1 ) && $f1 instanceof WP_Term ) {
                        $term_id = $f1->term_id;
                    }
                    if ( $term_id ) {
                        $ref_filter_1_ids[] = $term_id;
                    }
                }
            }
            $filter_attributes['data-reference-filter-1'] = implode( ',', $ref_filter_1_ids );

            // Reference Filter 2
            $ref_filter_2 = get_field( 'reference_filter_2', $post_item->ID );
            $ref_filter_2_ids = [];
            if ( $ref_filter_2 && is_array( value: $ref_filter_2 ) ) {
                foreach ( $ref_filter_2 as $f2 ) {
                    $term_id = null;
                    if ( is_numeric( value: $f2 ) ) {
                        $term_id = intval( $f2 );
                    } elseif ( is_object( value: $f2 ) && $f2 instanceof WP_Term ) {
                        $term_id = $f2->term_id;
                    }
                    if ( $term_id ) {
                        $ref_filter_2_ids[] = $term_id;
                    }
                }
            }
            $filter_attributes['data-reference-filter-2'] = implode( ',', $ref_filter_2_ids );

            // For display purposes
            $locations = get_field( 'reference_location', $post_item->ID );
            $location_names = [];

            if ( $locations ) {

                if ( ! is_array( value: $locations ) ) $locations = [$locations];

                foreach ( $locations as $location ) {

                    $location_names[] = esc_html(
                        get_field( 'reference_location_place', $location->ID ) ?: get_the_title( $location->ID )
                    );

                }

            }

            $reference_types = get_field( 'reference_type', $post_item->ID );
            $reference_type_values = [];

            if ( $reference_types && is_array( value: $reference_types ) ) {

                foreach ( $reference_types as $type ) {

                    $term = null;

                    // Handle different return formats
                    if ( is_numeric( value: $type ) ) {
                        $term = get_term( $type );
                    } elseif ( is_object( value: $type ) && $type instanceof WP_Term ) {
                        $term = $type;
                    } elseif ( is_string( value: $type ) ) {
                        $term = get_term_by( 'name', $type, 'reference-type' );
                    }

                    if ( $term && ! is_wp_error( $term ) ) {
                        $reference_type_values[] = esc_html( $term->name );
                    }

                }

            }
            ?>

            <li
                id="post-<?php echo esc_attr( $post_item->ID ); ?>"
                class="jpkcom-acf-ref-item border-bottom py-3"
                <?php echo esc_attr( $filter_attributes['data-reference-type'] ) ? 'data-reference-type="' . esc_attr( $filter_attributes['data-reference-type'] ) . '"' : ''; ?>
                <?php echo esc_attr( $filter_attributes['data-reference-filter-1'] ) ? 'data-reference-filter-1="' . esc_attr( $filter_attributes['data-reference-filter-1'] ) . '"' : ''; ?>
                <?php echo esc_attr( $filter_attributes['data-reference-filter-2'] ) ? 'data-reference-filter-2="' . esc_attr( $filter_attributes['data-reference-filter-2'] ) . '"' : ''; ?>
            >

                <div class="row align-items-center">
                    <div class="col-md-4 col-12 mb-1 mb-md-0">
                        <h5 class="fs-6 mb-0">
                            <a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>" class="text-decoration-none text-reset">
                                <?php echo esc_html( get_the_title( $post_item ) ); ?>
                            </a>
                        </h5>
                    </div>

                    <div class="col-md-4 col-12 text-md-center text-muted small">
                        <?php if ( ! empty( $location_names ) ) : ?>
                            <?php echo esc_html( implode( separator: ', ', array: $location_names ) ); ?>
                        <?php endif; ?>
                    </div>

                    <div class="col-md-4 col-12 text-md-end small text-uppercase fw-semibold text-secondary">
                        <?php if ( ! empty( $reference_type_values ) ) : ?>
                            <?php echo esc_html( implode( separator: ', ', array: $reference_type_values ) ); ?>
                        <?php endif; ?>
                    </div>
                </div>

                <?php
                $customers = get_field( 'reference_customer', $post_item->ID );
                $customer_names = [];

                if ( $customers ) {

                    if ( ! is_array( value: $customers ) ) $customers = [$customers];

                    foreach ( $customers as $customer ) {

                        $customer_names[] = esc_html(
                            get_the_title( $customer->ID )
                        );

                    }

                }

                $date_iso = get_the_date( 'Y-m-d', $post_item );
                $date_human = jpkcom_human_readable_relative_date( timestamp: get_the_date( 'U', $post_item ) );
                ?>

                <div class="row mt-1 small text-muted">
                    <div class="col-md-6 col-12">
                        <?php if ( ! empty( $customer_names ) ) : ?>
                            <?php echo esc_html( implode( separator: ', ', array: $customer_names ) ); ?>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 col-12 text-md-end">
                        <time datetime="<?php echo esc_attr( $date_iso ); ?>" class="date-posted">
                            <?php echo esc_html( $date_human ); ?>
                        </time>
                    </div>
                </div>

            </li>
        <?php endforeach; wp_reset_postdata(); ?>
    </ul>
</div>
