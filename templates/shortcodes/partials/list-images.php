<?php
/**
 * Shortcode partial: Image overlay layout (Bootstrap 5 Image Overlay Cards)
 *
 * Available variables:
 * - array $posts => Array of WP_Post objects
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-0 jpkcom-acf-ref-items">
    <?php foreach ( $posts as $post_item ) : setup_postdata( $post_item ); ?>
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

        // Get display data
        $post_thumbnail = get_the_post_thumbnail( $post_item->ID, 'jpkcom-acf-reference-header', [ 'class' => 'card-img' ] );

        // Check if this reference is featured
        $is_featured = get_field( 'reference_featured', $post_item->ID );
        $featured_class = $is_featured ? ' jpkcom-acf-reference--item-featured' : '';
        ?>

        <div
            class="col jpkcom-acf-ref-item"
            id="post-<?php echo esc_attr( $post_item->ID ); ?>"
            <?php echo esc_attr( $filter_attributes['data-reference-type'] ) ? 'data-reference-type="' . esc_attr( $filter_attributes['data-reference-type'] ) . '"' : ''; ?>
            <?php echo esc_attr( $filter_attributes['data-reference-filter-1'] ) ? 'data-reference-filter-1="' . esc_attr( $filter_attributes['data-reference-filter-1'] ) . '"' : ''; ?>
            <?php echo esc_attr( $filter_attributes['data-reference-filter-2'] ) ? 'data-reference-filter-2="' . esc_attr( $filter_attributes['data-reference-filter-2'] ) . '"' : ''; ?>
        >
            <div class="card border-0 rounded-0<?php echo esc_attr( $featured_class ); ?>">
                <?php if ( $post_thumbnail ) : ?>
                    <?php echo $post_thumbnail; ?>
                <?php endif; ?>

                <div class="card-img-overlay d-flex flex-column justify-content-end">
                    <div class="d-flex justify-content-between align-items-center bg-dark bg-opacity-75 p-2">
                        <h5 class="card-title text-white mb-0 me-2">
                            <a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>" class="stretched-link text-white text-decoration-none">
                                <?php echo esc_html( get_the_title( $post_item ) ); ?>
                            </a>
                        </h5>
                        <a href="<?php echo esc_url( get_permalink( $post_item ) ); ?>" class="btn btn-sm btn-light position-relative flex-shrink-0" style="z-index: 2;" aria-label="<?php echo esc_attr__( 'View detailed reference', 'jpkcom-acf-references' ); ?>">
                            <?php echo esc_html__( '→', 'jpkcom-acf-references' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

    <?php endforeach; wp_reset_postdata(); ?>
</div>
