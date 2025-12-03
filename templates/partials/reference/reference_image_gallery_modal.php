<?php
/**
 * Template Partial: reference_image_gallery_modal
 *
 * Bootstrap 5 Modal for image gallery lightbox
 *
 * Expected args:
 * - array $gallery_images => Gallery image array from ACF
 * - int $total_images => Total number of images
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

// Exit if accessed directly
defined( constant_name: 'ABSPATH' ) || exit;

// Get passed arguments
$gallery_images = $args['gallery_images'] ?? [];
$total_images = $args['total_images'] ?? 0;

if ( empty( $gallery_images ) ) {
    return;
}
?>

<!-- Gallery Modal -->
<div class="modal fade" id="galleryModal" tabindex="-1" aria-labelledby="galleryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="galleryModalLabel">
                    <?php echo esc_html__( 'Image Gallery:', 'jpkcom-acf-references' ); ?>
                    <span id="galleryModalImageCount">
                        <?php echo esc_html( sprintf( __( 'Image %d of %d', 'jpkcom-acf-references' ), 1, $total_images ) ); ?>
                    </span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'jpkcom-acf-references' ); ?>"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img
                    id="galleryModalImage"
                    src=""
                    alt=""
                    class="img-fluid w-100"
                    style="max-height: 80vh; object-fit: contain;"
                >
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div class="btn-group" role="group" aria-label="<?php echo esc_attr__( 'Gallery navigation', 'jpkcom-acf-references' ); ?>">
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="galleryModalPrev"
                        aria-label="<?php echo esc_attr__( 'Previous image', 'jpkcom-acf-references' ); ?>"
                    >
                        <span aria-hidden="true">&laquo;</span>
                        <?php echo esc_html__( 'Previous', 'jpkcom-acf-references' ); ?>
                    </button>
                    <button
                        type="button"
                        class="btn btn-outline-secondary"
                        id="galleryModalNext"
                        aria-label="<?php echo esc_attr__( 'Next image', 'jpkcom-acf-references' ); ?>"
                    >
                        <?php echo esc_html__( 'Next', 'jpkcom-acf-references' ); ?>
                        <span aria-hidden="true">&raquo;</span>
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <?php echo esc_html__( 'Close', 'jpkcom-acf-references' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Gallery Data (for JavaScript) -->
<script type="application/json" id="galleryModalData">
<?php
$gallery_data = [];
foreach ( $gallery_images as $index => $image ) {
    $gallery_data[] = [
        'index' => $index,
        'url' => wp_get_attachment_image_url( $image['ID'], 'jpkcom-acf-reference-gallery-modal' ),
        'alt' => $image['alt'] ? $image['alt'] : $image['title'],
    ];
}
echo wp_json_encode( $gallery_data );
?>
</script>
