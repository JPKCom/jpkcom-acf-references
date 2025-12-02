<?php
/**
 * Shortcode registration and template functions
 *
 * Registers and handles the following shortcodes:
 * - [jpkcom_acf_references_list] - Filtered reference listings
 * - [jpkcom_acf_references_types] - Reference types taxonomy display
 * - [jpkcom_acf_references_filter_1] - Reference filter 1 taxonomy display
 * - [jpkcom_acf_references_filter_2] - Reference filter 2 taxonomy display
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}


/**
 * Locate shortcode template file with override support
 *
 * Searches for template files in this order:
 * 1. Child theme: /wp-content/themes/child/jpkcom-acf-references/
 * 2. Parent theme: /wp-content/themes/parent/jpkcom-acf-references/
 * 3. MU plugin overrides: /wp-content/mu-plugins/jpkcom-acf-references-overrides/templates/
 * 4. Plugin templates: /wp-content/plugins/jpkcom-acf-references/templates/
 *
 * @since 1.0.0
 *
 * @param string $template_name Template filename (e.g., 'shortcodes/list.php').
 * @return string|false Full path to template file if found, false otherwise.
 */
if ( ! function_exists( function: 'jpkcom_acf_references_locate_template' ) ) {

    function jpkcom_acf_references_locate_template( string $template_name ): string|false {

        $search_paths = [
            trailingslashit( get_stylesheet_directory() ) . 'jpkcom-acf-references/' . $template_name,
            trailingslashit( get_template_directory() ) . 'jpkcom-acf-references/' . $template_name,
            trailingslashit( WPMU_PLUGIN_DIR ) . 'jpkcom-acf-references-overrides/templates/' . $template_name,
            trailingslashit( JPKCOM_ACFREFERENCES_PLUGIN_PATH ) . 'templates/' . $template_name,
        ];

        foreach ( $search_paths as $path ) {

            if ( file_exists( filename: $path ) ) {

                return $path;

            }

        }

        return false;

    }

}

/**
 * Register shortcodes on WordPress init
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'init', function(): void {

    /**
     * Shortcode: [jpkcom_acf_references_list]
     *
     * Displays a filtered list of reference postings with optional filtering and sorting.
     *
     * Attributes:
     * - type: CSV of reference-type taxonomy term IDs (e.g., "1,5,12")
     * - filter_1: CSV of reference-filter-1 taxonomy term IDs (e.g., "2,7")
     * - filter_2: CSV of reference-filter-2 taxonomy term IDs (e.g., "3,9")
     * - customer: CSV of customer post IDs (e.g., "12,34,56")
     * - location: CSV of location post IDs (e.g., "78,90")
     * - limit: Number of references to display (0 = no limit, shows all)
     * - sort: Sort order - "ASC" or "DSC" (default: "DSC")
     * - style: Inline CSS styles for the container
     * - class: CSS class(es) for the container
     * - title: Optional section headline
     * - show_filters: Display filter buttons ("true" or "false", default: "false")
     * - show_filter: Which filters to show - CSV of numbers 0-2 (0=reference-type, 1=reference-filter-1, 2=reference-filter-2, default: "0")
     * - reset_button: Display reset all filters button ("true" or "false", default: "false")
     * - filter_title_0: Custom label for filter 0 (reference-type), defaults to "Reference Type"
     * - filter_title_1: Custom label for filter 1 (reference-filter-1), defaults to "Filter 1"
     * - filter_title_2: Custom label for filter 2 (reference-filter-2), defaults to "Filter 2"
     * - layout: Display layout - "list" or "cards" (default: "list")
     *
     * Example usage:
     * [jpkcom_acf_references_list type="1,5" filter_1="2" customer="12" limit="5" show_filters="true" show_filter="0,1" reset_button="true" filter_title_0="Projekttyp" filter_title_1="Kategorie" layout="cards" class="my-references" title="Latest Projects"]
     *
     * @since 1.0.0
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML output.
     */
    add_shortcode( 'jpkcom_acf_references_list', function( $atts ): string {

        $defaults = [
            'type'           => '',     // CSV of reference-type taxonomy term IDs
            'filter_1'       => '',     // CSV of reference-filter-1 taxonomy term IDs
            'filter_2'       => '',     // CSV of reference-filter-2 taxonomy term IDs
            'customer'       => '',     // CSV of customer post IDs
            'location'       => '',     // CSV of location post IDs
            'limit'          => 0,      // 0 => no limit (we'll set -1 by default)
            'sort'           => 'DSC',  // ASC or DSC
            'style'          => '',
            'class'          => '',
            'title'          => '',
            'show_filters'   => 'false', // Display filter buttons (true/false)
            'show_filter'    => '0',    // Which filters to show (0=reference-type, 1=reference-filter-1, 2=reference-filter-2)
            'reset_button'   => 'false', // Display reset all filters button (true/false)
            'filter_title_0' => '',     // Custom label for filter 0 (reference-type)
            'filter_title_1' => '',     // Custom label for filter 1 (reference-filter-1)
            'filter_title_2' => '',     // Custom label for filter 2 (reference-filter-2)
            'layout'         => 'list', // Display layout: "list" or "cards"
        ];

        $atts = shortcode_atts( $defaults, (array) $atts, 'jpkcom_acf_references_list' );

        // Sanitize inputs
        $type_csv        = trim( string: (string) $atts['type'] );
        $filter_1_csv    = trim( string: (string) $atts['filter_1'] );
        $filter_2_csv    = trim( string: (string) $atts['filter_2'] );
        $customer_csv    = trim( string: (string) $atts['customer'] );
        $location_csv    = trim( string: (string) $atts['location'] );
        $limit           = intval( value: $atts['limit'] );
        $sort            = strtoupper( string: $atts['sort'] ) === 'ASC' ? 'ASC' : 'DESC';
        $style           = trim( string: (string) $atts['style'] );
        $class           = trim( string: (string) $atts['class'] );
        $title           = trim( string: (string) $atts['title'] );
        $show_filters    = strtolower( string: trim( string: (string) $atts['show_filters'] ) ) === 'true';
        $show_filter     = trim( string: (string) $atts['show_filter'] );
        $reset_button    = strtolower( string: trim( string: (string) $atts['reset_button'] ) ) === 'true';
        $filter_title_0  = trim( string: (string) $atts['filter_title_0'] );
        $filter_title_1  = trim( string: (string) $atts['filter_title_1'] );
        $filter_title_2  = trim( string: (string) $atts['filter_title_2'] );
        $layout          = in_array( strtolower( trim( (string) $atts['layout'] ) ), ['list', 'cards'] ) ? strtolower( trim( (string) $atts['layout'] ) ) : 'list';

        // Build WP_Query args
        $query_args = [
            'post_type'      => 'reference',
            'post_status'    => 'publish',
            'posts_per_page' => $limit > 0 ? $limit : -1,
            'meta_key'       => 'reference_featured',
            'orderby'        => [
                'meta_value_num' => 'DESC',
                'date'           => $sort,
            ],
        ];

        // Build meta_query for ACF-stored arrays (checkbox/post_object stored serialized)
        $meta_query = [
            'relation' => 'AND',
            [
                'key'     => 'reference_featured',
                'compare' => 'EXISTS',
            ],
            [
                'relation' => 'OR',
                [
                    'key'     => 'reference_expiry_date',
                    'value'   => date( format: 'Y-m-d' ),
                    'compare' => '>=',
                    'type'    => 'DATE',
                ],
                [
                    'key'     => 'reference_expiry_date',
                    'compare' => 'NOT EXISTS',
                ],
                [
                    'key'     => 'reference_expiry_date',
                    'value'   => '',
                    'compare' => '=',
                ],
            ],
        ];

        // reference_type: CSV of term IDs (e.g. 1,5,12)
        if ( $type_csv !== '' ) {

            $want = array_filter( array: array_map( callback: 'trim', array: explode( separator: ',', string: $type_csv ) ) );

            if ( ! empty( $want ) ) {

                // We add a meta_query clause for each wanted value with LIKE on serialized value.
                $type_clauses = [ 'relation' => 'OR' ];

                foreach ( $want as $val ) {

                    // Serialized arrays will contain "...\"VALUE\"..." so match with quotes.
                    $type_clauses[] = [
                        'key'     => 'reference_type',
                        'value'   => '"' . sanitize_text_field( $val ) . '"',
                        'compare' => 'LIKE',
                    ];

                }

                $meta_query[] = $type_clauses;

            }

        }

        // reference_filter_1: CSV of term IDs (e.g. 2,7)
        if ( $filter_1_csv !== '' ) {

            $want = array_filter( array: array_map( callback: 'trim', array: explode( separator: ',', string: $filter_1_csv ) ) );

            if ( ! empty( $want ) ) {

                // We add a meta_query clause for each wanted value with LIKE on serialized value.
                $filter_1_clauses = [ 'relation' => 'OR' ];

                foreach ( $want as $val ) {

                    // Serialized arrays will contain "...\"VALUE\"..." so match with quotes.
                    $filter_1_clauses[] = [
                        'key'     => 'reference_filter_1',
                        'value'   => '"' . sanitize_text_field( $val ) . '"',
                        'compare' => 'LIKE',
                    ];

                }

                $meta_query[] = $filter_1_clauses;

            }

        }

        // reference_filter_2: CSV of term IDs (e.g. 3,9)
        if ( $filter_2_csv !== '' ) {

            $want = array_filter( array: array_map( callback: 'trim', array: explode( separator: ',', string: $filter_2_csv ) ) );

            if ( ! empty( $want ) ) {

                // We add a meta_query clause for each wanted value with LIKE on serialized value.
                $filter_2_clauses = [ 'relation' => 'OR' ];

                foreach ( $want as $val ) {

                    // Serialized arrays will contain "...\"VALUE\"..." so match with quotes.
                    $filter_2_clauses[] = [
                        'key'     => 'reference_filter_2',
                        'value'   => '"' . sanitize_text_field( $val ) . '"',
                        'compare' => 'LIKE',
                    ];

                }

                $meta_query[] = $filter_2_clauses;

            }

        }

        // Customer filter: CSV of post IDs
        if ( $customer_csv !== '' ) {

            $ids = array_filter( array: array_map( callback: 'absint', array: explode( separator: ',', string: $customer_csv ) ) );

            if ( ! empty( $ids ) ) {

                $customer_clauses = [ 'relation' => 'OR' ];
                foreach ( $ids as $id ) {
                    $customer_clauses[] = [
                        'key'     => 'reference_customer',
                        'value'   => '"' . $id . '"',
                        'compare' => 'LIKE',
                    ];
                }

                $meta_query[] = $customer_clauses;

            }

        }

        // location filter: CSV of post IDs
        if ( $location_csv !== '' ) {

            $ids = array_filter( array: array_map( callback: 'absint', array: explode( separator: ',', string: $location_csv ) ) );

            if ( ! empty( $ids ) ) {

                $location_clauses = [ 'relation' => 'OR' ];

                foreach ( $ids as $id ) {

                    $location_clauses[] = [
                        'key'     => 'reference_location',
                        'value'   => '"' . $id . '"',
                        'compare' => 'LIKE',
                    ];

                }

                $meta_query[] = $location_clauses;

            }

        }

        // Only add meta_query if there are meaningful subclauses (more than the relation key)
        if ( count( value: $meta_query ) > 1 ) {

            $query_args['meta_query'] = $meta_query;

        }

        /**
         * Filter reference listing query arguments before execution
         *
         * @since 1.0.0
         *
         * @param array $query_args WP_Query arguments array.
         * @param array $atts       Shortcode attributes.
         */
        $query_args = apply_filters( 'jpkcom_acf_references_list_query_args', $query_args, $atts );

        $q = new WP_Query( $query_args );

        // Prepare filter data if show_filters is enabled
        $filter_data = [];
        if ( $show_filters ) {

            // Parse which filters to show (0, 1, 2)
            // Note: We use a custom callback to keep 0 (array_filter removes 0 by default as falsy)
            $active_filters = array_filter(
                array: array_map( callback: 'intval', array: explode( separator: ',', string: $show_filter ) ),
                callback: function( $val ) { return $val >= 0; }
            );

            // Build filter data for each active filter
            foreach ( $active_filters as $filter_num ) {

                $filter_config = null;

                switch ( $filter_num ) {
                    case 0:
                        $filter_config = [
                            'id'       => 'reference-type',
                            'taxonomy' => 'reference-type',
                            'field'    => 'reference_type',
                            'label'    => ! empty( $filter_title_0 ) ? $filter_title_0 : __( 'Reference Type', 'jpkcom-acf-references' ),
                        ];
                        break;

                    case 1:
                        $filter_config = [
                            'id'       => 'reference-filter-1',
                            'taxonomy' => 'reference-filter-1',
                            'field'    => 'reference_filter_1',
                            'label'    => ! empty( $filter_title_1 ) ? $filter_title_1 : __( 'Filter 1', 'jpkcom-acf-references' ),
                        ];
                        break;

                    case 2:
                        $filter_config = [
                            'id'       => 'reference-filter-2',
                            'taxonomy' => 'reference-filter-2',
                            'field'    => 'reference_filter_2',
                            'label'    => ! empty( $filter_title_2 ) ? $filter_title_2 : __( 'Filter 2', 'jpkcom-acf-references' ),
                        ];
                        break;
                }

                if ( $filter_config ) {

                    // Get all terms for this taxonomy
                    $terms = get_terms( [
                        'taxonomy'   => $filter_config['taxonomy'],
                        'hide_empty' => true,
                    ] );

                    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {

                        $filter_config['terms'] = $terms;
                        $filter_data[] = $filter_config;

                    }

                }

            }

        }

        // Prepare args for template
        $tpl_args = [
            'posts'        => $q->posts,
            'query'        => $q,
            'atts'         => $atts,
            'style'        => $style,
            'class'        => $class,
            'title'        => $title,
            'show_filters' => $show_filters,
            'filter_data'  => $filter_data,
            'reset_button' => $reset_button,
            'layout'       => $layout,
        ];

        // Render template via buffer. Use your loader to find the template.
        $template_name = 'shortcodes/list.php';
        $path = jpkcom_acf_references_locate_template( template_name: $template_name );

        ob_start();
        if ( $path ) {

            // Make variables available inside template
            extract( array: $tpl_args, flags: EXTR_SKIP );
            include $path;

        } else {

            // Fallback inline markup if no template present
            ?>
            <div class="jpkcom-acf-references--list<?php if ( ! empty( $class ) ) echo ' ' . esc_attr( $class ); ?>" <?php if ( ! empty( $style ) ) echo 'style="' . esc_attr( $style ) . '"'; ?>>

                <?php if ( ! empty( $title ) ) : ?>
                    <h3 class="mb-3"><?php echo esc_html( $title ); ?></h3>
                <?php endif; ?>

                <?php if ( $q->have_posts() ) : ?>
                    <ul class="list-unstyled">
                        <?php foreach ( $q->posts as $post_item ) : setup_postdata( $post_item ); ?>
                            <li id="post-<?php echo esc_attr( $post_item->ID ); ?>" class="border-bottom py-3">

                                <?php
                                // Locations
                                $locations = get_field( 'reference_location', $post_item->ID );
                                $location_names = [];

                                if ( $locations ) {
                                    if ( ! is_array( value: $locations ) ) $locations = [ $locations ];
                                    foreach ( $locations as $location ) {
                                        $location_names[] = esc_html(
                                            get_field( 'reference_location_place', $location->ID ) ?: get_the_title( $location->ID )
                                        );
                                    }
                                }

                                // Reference Types
                                $reference_types = get_field( 'reference_type', $post_item->ID );
                                $reference_type_values = [];
                                if ( $reference_types && is_array( value: $reference_types ) ) {
                                    foreach ( $reference_types as $type ) {
                                        if ( is_array( value: $type ) && isset( $type['label'] ) ) {
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
                                // Customers
                                $customers = get_field( 'reference_customer', $post_item->ID );
                                $customer_names = [];
                                if ( $customers ) {
                                    if ( ! is_array( value: $customers ) ) $customers = [ $customers ];
                                    foreach ( $customers as $customer ) {
                                        $customer_names[] = esc_html( get_the_title( $customer->ID ) );
                                    }
                                }

                                // Date
                                $date_iso   = get_the_date( 'Y-m-d', $post_item );
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
                <?php else : ?>

                    <p class="text-muted mb-0"><?php esc_html_e( 'No references found.', 'jpkcom-acf-references' ); ?></p>

                <?php endif; ?>

            </div>
            <?php
        }

        return (string) ob_get_clean();

    } );

    /**
     * Shortcode: [jpkcom_acf_references_types]
     *
     * Displays reference types (taxonomy terms) as expandable <details> elements.
     * Shows term name as summary and term description as content.
     *
     * Attributes:
     * - id: CSV of term IDs to display (optional, shows all if omitted)
     * - style: Inline CSS styles for the container
     * - class: CSS class(es) for the container
     * - title: Optional section headline
     *
     * Example usage:
     * [jpkcom_acf_references_types id="1,2,3" class="reference-types" title="Reference Types"]
     *
     * @since 1.0.0
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML output.
     */
    add_shortcode( 'jpkcom_acf_references_types', function( $atts ): string {

        $defaults = [
            'id'    => '', // CSV of term IDs (optional)
            'style' => '',
            'class' => '',
            'title' => '',
        ];

        $atts = shortcode_atts( $defaults, (array) $atts, 'jpkcom_acf_references_types' );

        $ids_csv = trim( string: (string) $atts['id'] );
        $style   = trim( string: (string) $atts['style'] );
        $class   = trim( string: (string) $atts['class'] );
        $title   = trim( string: (string) $atts['title'] );

        $args = [
            'taxonomy'   => 'reference-type',
            'hide_empty' => false,
        ];

        if ( $ids_csv !== '' ) {

            $ids = array_filter( array: array_map( callback: 'absint', array: explode( separator: ',', string: $ids_csv ) ) );

            if ( ! empty( $ids ) ) {

                $args['include'] = $ids;

            }

        }

        $terms = get_terms( $args );

        // Template name
        $template_name = 'shortcodes/types.php';
        $path = jpkcom_acf_references_locate_template( template_name: $template_name );

        ob_start();

        if ( $path ) {

            $tpl_args = [
                'terms' => $terms,
                'atts'  => $atts,
                'style' => $style,
                'class' => $class,
                'title' => $title,
            ];

            extract( array: $tpl_args, flags: EXTR_SKIP );
            include $path;

        } else {

            // Fallback output:
            if ( $title ) {

                echo '<h2>' . esc_html( $title ) . '</h2>';

            }

            if ( empty( $terms ) ) {

                echo '<p class="text-muted">' . esc_html__( 'No types found.', 'jpkcom-acf-references' ) . '</p>';

            } else {

                echo '<div class="jpkcom-acf-references--type';

                if ( ! empty( $class ) ) {
                    echo ' ' . esc_attr( $class );
                }

                echo '"';

                if ( ! empty( $style ) ) {

                    echo ' style="' . esc_attr( $style ) . '"';

                }

                echo '>';

                if ( ! empty( $title ) ) {

                    echo '<h3 class="mb-3">' . esc_html( $title ) . '</h3>';

                }

                foreach ( $terms as $term ) {

                    $summary = esc_html( $term->name );
                    $desc = wp_kses_post( term_description( $term->term_id, $term->taxonomy ) );
                    echo '<details name="jpkcom-acf-references-type" class="border rounded p-3 mb-3">';
                    echo '<summary class="px-3 fs-5"><h4 class="d-inline fs-5">' . $summary . '</h4></summary>';
                    echo '<div class="p-3">' . ( $desc ?: '<p class="text-muted">' . esc_html__( 'No description.', 'jpkcom-acf-references' ) . '</p>' ) . '</div>';
                    echo '</details>';

                }

                echo '</div>';

            }

        }

        return (string) ob_get_clean();

    } );

    /**
     * Shortcode: [jpkcom_acf_references_filter_1]
     *
     * Displays reference filter 1 (taxonomy terms) as expandable <details> elements.
     * Shows term name as summary and term description as content.
     *
     * Attributes:
     * - id: CSV of term IDs to display (optional, shows all if omitted)
     * - style: Inline CSS styles for the container
     * - class: CSS class(es) for the container
     * - title: Optional section headline
     *
     * Example usage:
     * [jpkcom_acf_references_filter_1 id="1,2,3" class="reference-filter-1" title="Filter 1"]
     *
     * @since 1.0.0
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML output.
     */
    add_shortcode( 'jpkcom_acf_references_filter_1', function( $atts ): string {

        $defaults = [
            'id'    => '', // CSV of term IDs (optional)
            'style' => '',
            'class' => '',
            'title' => '',
        ];

        $atts = shortcode_atts( $defaults, (array) $atts, 'jpkcom_acf_references_filter_1' );

        $ids_csv = trim( string: (string) $atts['id'] );
        $style   = trim( string: (string) $atts['style'] );
        $class   = trim( string: (string) $atts['class'] );
        $title   = trim( string: (string) $atts['title'] );

        $args = [
            'taxonomy'   => 'reference-filter-1',
            'hide_empty' => false,
        ];

        if ( $ids_csv !== '' ) {

            $ids = array_filter( array: array_map( callback: 'absint', array: explode( separator: ',', string: $ids_csv ) ) );

            if ( ! empty( $ids ) ) {

                $args['include'] = $ids;

            }

        }

        $terms = get_terms( $args );

        // Template name
        $template_name = 'shortcodes/filter-1.php';
        $path = jpkcom_acf_references_locate_template( template_name: $template_name );

        ob_start();

        if ( $path ) {

            $tpl_args = [
                'terms' => $terms,
                'atts'  => $atts,
                'style' => $style,
                'class' => $class,
                'title' => $title,
            ];

            extract( array: $tpl_args, flags: EXTR_SKIP );
            include $path;

        } else {

            // Fallback output:
            if ( $title ) {

                echo '<h2>' . esc_html( $title ) . '</h2>';

            }

            if ( empty( $terms ) ) {

                echo '<p class="text-muted">' . esc_html__( 'No filter terms found.', 'jpkcom-acf-references' ) . '</p>';

            } else {

                echo '<div class="jpkcom-acf-references--filter-1';

                if ( ! empty( $class ) ) {
                    echo ' ' . esc_attr( $class );
                }

                echo '"';

                if ( ! empty( $style ) ) {

                    echo ' style="' . esc_attr( $style ) . '"';

                }

                echo '>';

                if ( ! empty( $title ) ) {

                    echo '<h3 class="mb-3">' . esc_html( $title ) . '</h3>';

                }

                foreach ( $terms as $term ) {

                    $summary = esc_html( $term->name );
                    $desc = wp_kses_post( term_description( $term->term_id, $term->taxonomy ) );
                    echo '<details name="jpkcom-acf-references-filter-1" class="border rounded p-3 mb-3">';
                    echo '<summary class="px-3 fs-5"><h4 class="d-inline fs-5">' . $summary . '</h4></summary>';
                    echo '<div class="p-3">' . ( $desc ?: '<p class="text-muted">' . esc_html__( 'No description.', 'jpkcom-acf-references' ) . '</p>' ) . '</div>';
                    echo '</details>';

                }

                echo '</div>';

            }

        }

        return (string) ob_get_clean();

    } );

    /**
     * Shortcode: [jpkcom_acf_references_filter_2]
     *
     * Displays reference filter 2 (taxonomy terms) as expandable <details> elements.
     * Shows term name as summary and term description as content.
     *
     * Attributes:
     * - id: CSV of term IDs to display (optional, shows all if omitted)
     * - style: Inline CSS styles for the container
     * - class: CSS class(es) for the container
     * - title: Optional section headline
     *
     * Example usage:
     * [jpkcom_acf_references_filter_2 id="1,2,3" class="reference-filter-2" title="Filter 2"]
     *
     * @since 1.0.0
     *
     * @param array|string $atts Shortcode attributes.
     * @return string Rendered HTML output.
     */
    add_shortcode( 'jpkcom_acf_references_filter_2', function( $atts ): string {

        $defaults = [
            'id'    => '', // CSV of term IDs (optional)
            'style' => '',
            'class' => '',
            'title' => '',
        ];

        $atts = shortcode_atts( $defaults, (array) $atts, 'jpkcom_acf_references_filter_2' );

        $ids_csv = trim( string: (string) $atts['id'] );
        $style   = trim( string: (string) $atts['style'] );
        $class   = trim( string: (string) $atts['class'] );
        $title   = trim( string: (string) $atts['title'] );

        $args = [
            'taxonomy'   => 'reference-filter-2',
            'hide_empty' => false,
        ];

        if ( $ids_csv !== '' ) {

            $ids = array_filter( array: array_map( callback: 'absint', array: explode( separator: ',', string: $ids_csv ) ) );

            if ( ! empty( $ids ) ) {

                $args['include'] = $ids;

            }

        }

        $terms = get_terms( $args );

        // Template name
        $template_name = 'shortcodes/filter-2.php';
        $path = jpkcom_acf_references_locate_template( template_name: $template_name );

        ob_start();

        if ( $path ) {

            $tpl_args = [
                'terms' => $terms,
                'atts'  => $atts,
                'style' => $style,
                'class' => $class,
                'title' => $title,
            ];

            extract( array: $tpl_args, flags: EXTR_SKIP );
            include $path;

        } else {

            // Fallback output:
            if ( $title ) {

                echo '<h2>' . esc_html( $title ) . '</h2>';

            }

            if ( empty( $terms ) ) {

                echo '<p class="text-muted">' . esc_html__( 'No filter terms found.', 'jpkcom-acf-references' ) . '</p>';

            } else {

                echo '<div class="jpkcom-acf-references--filter-2';

                if ( ! empty( $class ) ) {
                    echo ' ' . esc_attr( $class );
                }

                echo '"';

                if ( ! empty( $style ) ) {

                    echo ' style="' . esc_attr( $style ) . '"';

                }

                echo '>';

                if ( ! empty( $title ) ) {

                    echo '<h3 class="mb-3">' . esc_html( $title ) . '</h3>';

                }

                foreach ( $terms as $term ) {

                    $summary = esc_html( $term->name );
                    $desc = wp_kses_post( term_description( $term->term_id, $term->taxonomy ) );
                    echo '<details name="jpkcom-acf-references-filter-2" class="border rounded p-3 mb-3">';
                    echo '<summary class="px-3 fs-5"><h4 class="d-inline fs-5">' . $summary . '</h4></summary>';
                    echo '<div class="p-3">' . ( $desc ?: '<p class="text-muted">' . esc_html__( 'No description.', 'jpkcom-acf-references' ) . '</p>' ) . '</div>';
                    echo '</details>';

                }

                echo '</div>';

            }

        }

        return (string) ob_get_clean();

    } );

} );
