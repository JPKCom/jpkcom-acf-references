<?php
/**
 * Archive query modification functions
 *
 * Modifies the main query for reference archives to:
 * - Filter out expired references (based on reference_expiry_date)
 * - Sort by featured status (reference_featured) then by date
 * - Only show published references
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}


/**
 * Modify reference archive query to exclude expired references and sort by featured status
 *
 * Applied to the main query on reference archive pages only (not admin).
 * Filters references to show only:
 * - References without expiry date
 * - References with empty expiry date
 * - References with expiry date >= today
 *
 * Sorting order:
 * 1. Featured references first (reference_featured field, DESC)
 * 2. Then by publication date (DESC)
 *
 * @since 1.0.0
 *
 * @param WP_Query $query The WordPress query object.
 * @return void Modifies query by reference.
 */
add_action( 'pre_get_posts', function( $query ): void {

    if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'reference' ) ) {

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

        $query->set( 'meta_query', $meta_query );

        $query->set( 'meta_key', 'reference_featured' );
        $query->set( 'orderby', [
            'meta_value_num' => 'DESC',
            'date'           => 'DESC',
        ] );
    }

});
