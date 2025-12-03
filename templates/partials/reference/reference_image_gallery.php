<?php
/**
 * Template Partial: reference_image_gallery
 *
 * Displays image gallery thumbnails with modal lightbox functionality
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

$gallery_images = get_field( 'reference_image_gallery' );

if ( $gallery_images && is_array( $gallery_images ) ) :
    $total_images = count( $gallery_images );
?>

    <div class="jpkcom-acf-reference-gallery mb-4">
        <h3 class="h4 mb-3"><?php echo esc_html__( 'Image Gallery', 'jpkcom-acf-references' ); ?></h3>

        <div class="row g-3">
            <?php foreach ( $gallery_images as $index => $image ) :
                $image_number = $index + 1;
                $thumbnail_url = wp_get_attachment_image_url( $image['ID'], 'jpkcom-acf-reference-gallery-thumb' );
                $modal_url = wp_get_attachment_image_url( $image['ID'], 'jpkcom-acf-reference-gallery-modal' );
                $alt_text = $image['alt'] ? $image['alt'] : $image['title'];
            ?>
                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                    <button
                        type="button"
                        class="btn p-0 border-0 jpkcom-acf-gallery-thumb-btn"
                        data-bs-toggle="modal"
                        data-bs-target="#galleryModal"
                        data-image-index="<?php echo esc_attr( $index ); ?>"
                        data-image-url="<?php echo esc_url( $modal_url ); ?>"
                        data-image-alt="<?php echo esc_attr( $alt_text ); ?>"
                        aria-label="<?php echo esc_attr( sprintf( __( 'Open image %d of %d in lightbox', 'jpkcom-acf-references' ), $image_number, $total_images ) ); ?>"
                    >
                        <img
                            src="<?php echo esc_url( $thumbnail_url ); ?>"
                            alt="<?php echo esc_attr( $alt_text ); ?>"
                            class="img-thumbnail w-100 h-100 object-fit-cover jpkcom-acf-gallery-thumb"
                            loading="lazy"
                        >
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    // Include modal partial
    jpkcom_acf_references_get_template_part(
        slug: 'partials/reference/reference_image_gallery_modal',
        name: null,
        args: [
            'gallery_images' => $gallery_images,
            'total_images' => $total_images
        ]
    );
    ?>

<?php endif; ?>
