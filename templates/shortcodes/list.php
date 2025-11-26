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
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

if ( ! isset( $posts ) || ! is_array( value: $posts ) ) {

    echo '<p class="text-muted">' . esc_html__( 'No references to display.', 'jpkcom-acf-references' ) . '</p>';

    return;

}
?>

<div class="jpkcom-acf-references--list<?php if ( ! empty( $class ) ) echo ' ' . esc_attr( $class ); ?>" <?php if ( ! empty( $style ) ) echo 'style="' . esc_attr( $style ) . '"'; ?>>

    <?php if ( ! empty( $title ) ) : ?>
        <h3 class="mb-3"><?php echo esc_html($title); ?></h3>
    <?php endif; ?>

    <ul class="list-unstyled">
        <?php foreach ( $posts as $post_item)  : setup_postdata( $post_item ); ?>
            <li id="post-<?php echo esc_attr( $post_item->ID ); ?>" class="border-bottom py-3">

                <?php
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

                        if (is_array( value: $type ) && isset( $type['label'] )) {

                            $reference_type_values[] = esc_html( $type['label'] );

                        } elseif ( is_string( value: $type ) ) {

                            $reference_type_values[] = esc_html( $type );

                        }

                    }

                }
                ?>

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
