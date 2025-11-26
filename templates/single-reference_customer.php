<?php
/**
 * Template: Single Reference Customer
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

get_header();

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
?>

<main id="reference-<?php the_ID(); ?>" <?php post_class( 'jpkcom-acf-reference--single-customer container mx-auto py-12' ); ?>>

<div class="container my-5">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <h1 class="display-5 mb-3"><?php the_title(); ?></h1>

            <div class="mb-4">
                <?php the_content(); ?>
            </div>

            <?php jpkcom_render_acf_fields(); ?>

            <?php
                if ( current_user_can( 'edit_post', get_the_ID() ) ) {

                    edit_post_link( __( 'Edit customer', 'jpkcom-acf-references' ), '<p class="edit-link">', '</p>' );

                }
            ?>

        </div>
    </div>
</div>

</main>

<?php
    endwhile;
endif;

get_footer();
