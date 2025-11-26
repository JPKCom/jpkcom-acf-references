<?php
/**
 * Template: Archive References
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

get_header();
?>

<div id="content" class="jpkcom-acf-reference--archive site-content container pt-4 pb-5">
    <div id="primary" class="content-area">

        <main id="main" class="site-main">

            <!-- Header -->
            <div class="p-5 text-center bg-body-tertiary rounded mb-4">
                <h1 class="entry-title display-4 mb-4"><?php echo __( 'Current reference offers', 'jpkcom-acf-references' ); ?></h1>
            </div>

            <!-- Reference List -->
            <?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

                <article id="reference-<?php the_ID(); ?>" class="jpkcom-acf-reference--item card horizontal p-0 mb-4<?php if ( get_field('reference_featured') ) { echo ' jpkcom-acf-reference--item-featured'; } ?>">

                    <div class="row g-0">

                        <?php if ( has_post_thumbnail() ) : ?>
                            <div class="col-lg-6 col-xl-5 col-xxl-4">
                            <a href="<?php the_permalink(); ?>">
                                <?php the_post_thumbnail( 'jpkcom-acf-reference-16x9', array( 'class' => 'jpkcom-acf-reference--16x9 card-img-lg-start' ) ); ?>
                            </a>
                            </div>
                        <?php endif; ?>

                        <div class="col">

                            <header class="card-header">

                                <div class="d-flex justify-content-start gap-3">

                                    <?php if ( get_field('reference_featured') ) { ?>
                                        <p class="sticky-badge"><span class="badge text-bg-danger"><i class="fa-solid fa-star"></i></span></p>
                                    <?php } ?>

                                    <a class="text-body text-decoration-none" href="<?php the_permalink(); ?>">
                                        <?php the_title('<h2 class="reference-title h4">', '</h2>'); ?>
                                    </a>

                                </div>

                                <?php if ('reference' === get_post_type()) : ?>
                                <?php jpkcom_acf_references_get_template_part( slug: 'partials/layout/meta' ); ?>
                                <?php endif; ?>

                            </header>

                            <div class="card-body">

                                <?php if ( get_field( 'reference_customer' ) || get_field( 'reference_location' ) || get_field( 'reference_type' ) ) { ?>
                                <div class="alert alert-light d-flex p-3">
                                    <ul class="list-unstyled d-md-flex w-100 justify-content-md-between align-items-md-stretch gap-3">

                                    <?php jpkcom_acf_references_get_template_part( slug: 'partials/archive/reference_type' ); ?>

                                    <?php jpkcom_acf_references_get_template_part( slug: 'partials/archive/reference_customer' ); ?>

                                    <?php jpkcom_acf_references_get_template_part( slug: 'partials/archive/reference_location' ); ?>

                                    </ul>
                                </div>
                                <?php } ?>

                                <p class="card-text">
                                    <a class="text-body text-decoration-none" href="<?php the_permalink(); ?>">
                                        <?php echo wp_kses_post( get_field( 'reference_short_description' ) ); ?>
                                    </a>
                                </p>

                            </div>

                            <footer class="card-footer text-end">

                                <a href="<?php the_permalink(); ?>" class="btn btn-primary stretched-link"><?php echo __( 'View details…', 'jpkcom-acf-references' ); ?></a>

                            </footer>

                        </div>

                    </div>

                </article>

            <?php endwhile; ?>

            <?php jpkcom_acf_references_pagination(); ?>

            <?php else : ?>
                <div class="alert alert-info" role="alert">
                    <p><?php echo __( 'There are currently no reference offers available.', 'jpkcom-acf-references' ); ?></p>
                </div>
            <?php endif; ?>

        </main>

    </div>
</div>

<?php get_footer(); ?>
