<?php
/**
 * Template Partial: pagination-page
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;
?>

<nav aria-label="<?php echo esc_html__( 'Reference page navigation', 'jpkcom-acf-references' ); ?>">
    <ul class="pagination justify-content-center flex-wrap gap-0 row-gap-2">
        <li class="page-item">
            <a href="<?php echo esc_url( get_post_type_archive_link( 'reference' ) ); ?>" class="page-link">&larr; <?php echo esc_html__( 'Back to overview', 'jpkcom-acf-references' ); ?></a>
        </li>
        <?php
        $prev_post = get_previous_post();
        if ( $prev_post ) : ?>
        <li class="page-item">
            <a href="<?php echo esc_url( get_permalink( $prev_post ) ); ?>" class="page-link">&larr; <?php echo esc_html( get_the_title( $prev_post ) ); ?></a>
        </li>
        <?php endif;

        $next_post = get_next_post();
        if ( $next_post ) : ?>
        <li class="page-item">
            <a href="<?php echo esc_url( get_permalink( $next_post ) ); ?>" class="page-link"><?php echo esc_html( get_the_title( $next_post ) ); ?> &rarr;</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
