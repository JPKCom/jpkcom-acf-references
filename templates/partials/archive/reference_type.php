<?php
/**
 * Template Partial: reference_type
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<?php if ( get_field( 'reference_type' ) ) { ?>
    <li class="d-block">
        <strong><?php echo __( 'Type', 'jpkcom-acf-references' ); ?>:</strong><br>
        <?php
        $types = get_field( 'reference_type' );
        if ( $types && is_array( value: $types ) ) {
            $total = count( value: $types );
            $i = 0;
            foreach ( $types as $type ) {
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
                    echo esc_html( $term->name );
                }

                $i++;
                if ( $i < $total ) {
                    echo ',<br>';
                }
            }
        }
        ?>
    </li>
<?php } ?>
