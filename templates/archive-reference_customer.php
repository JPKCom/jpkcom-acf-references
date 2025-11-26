<?php
/**
 * Template: Archive Reference Customer
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
                <h1 class="entry-title display-4 mb-4"><?php echo __( 'Customer overview', 'jpkcom-acf-references' ); ?></h1>
            </div>

            <!-- Customers List -->
            <?php if (have_posts()) : while (have_posts()) : the_post(); ?>

                <article id="reference-<?php the_ID(); ?>" class="jpkcom-acf-reference--item card horizontal p-0 mb-4">

                    <div class="row g-0">

                        <div class="col">

                            <header class="card-header">

                                <div class="d-flex justify-content-start gap-3">

                                    <a class="text-body text-decoration-none" href="<?php the_permalink(); ?>">
                                        <?php the_title('<h2 class="reference-title h4">', '</h2>'); ?>
                                    </a>

                                </div>

                            </header>

                            <div class="card-body">

                                <?php jpkcom_render_acf_fields(); ?>

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
                    <p><?php echo __( 'There are currently no customers available.', 'jpkcom-acf-references' ); ?></p>
                </div>
            <?php endif; ?>

        </main>

    </div>
</div>

<?php get_footer(); ?>
