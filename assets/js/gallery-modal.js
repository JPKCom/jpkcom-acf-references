/**
 * Gallery Modal Navigation
 *
 * Handles image gallery modal functionality with keyboard navigation
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initGalleryModal);
    } else {
        initGalleryModal();
    }

    function initGalleryModal() {
        const modal = document.getElementById('galleryModal');
        if (!modal) return;

        const modalImage = document.getElementById('galleryModalImage');
        const modalImageCount = document.getElementById('galleryModalImageCount');
        const prevBtn = document.getElementById('galleryModalPrev');
        const nextBtn = document.getElementById('galleryModalNext');
        const galleryDataElement = document.getElementById('galleryModalData');

        if (!modalImage || !modalImageCount || !prevBtn || !nextBtn || !galleryDataElement) {
            return;
        }

        // Parse gallery data from JSON
        let galleryData = [];
        try {
            galleryData = JSON.parse(galleryDataElement.textContent);
        } catch (e) {
            console.error('Failed to parse gallery data:', e);
            return;
        }

        const totalImages = galleryData.length;
        let currentIndex = 0;

        // Get translation strings from modal title
        const modalTitle = document.getElementById('galleryModalLabel');
        const titleParts = modalTitle ? modalTitle.textContent.split(':') : [];
        const galleryTitlePrefix = titleParts[0] ? titleParts[0].trim() + ':' : 'Image Gallery:';

        // Extract template for "Image X of Y" - we'll parse it from the initial count text
        const initialCountText = modalImageCount.textContent.trim();
        const countTemplate = initialCountText.replace(/\d+/g, '%d');

        /**
         * Update modal with current image
         */
        function updateModal() {
            const imageData = galleryData[currentIndex];
            if (!imageData) return;

            // Update image
            modalImage.src = imageData.url;
            modalImage.alt = imageData.alt;

            // Update counter - replace placeholders with actual numbers
            const countText = countTemplate
                .replace('%d', currentIndex + 1)
                .replace('%d', totalImages);
            modalImageCount.textContent = countText;

            // Update button states
            prevBtn.disabled = currentIndex === 0;
            nextBtn.disabled = currentIndex === totalImages - 1;

            // Update ARIA labels
            prevBtn.setAttribute('aria-disabled', currentIndex === 0 ? 'true' : 'false');
            nextBtn.setAttribute('aria-disabled', currentIndex === totalImages - 1 ? 'true' : 'false');
        }

        /**
         * Go to previous image
         */
        function showPrevious() {
            if (currentIndex > 0) {
                currentIndex--;
                updateModal();
            }
        }

        /**
         * Go to next image
         */
        function showNext() {
            if (currentIndex < totalImages - 1) {
                currentIndex++;
                updateModal();
            }
        }

        // Event listeners for navigation buttons
        prevBtn.addEventListener('click', showPrevious);
        nextBtn.addEventListener('click', showNext);

        // Thumbnail click handler
        document.querySelectorAll('.jpkcom-acf-gallery-thumb-btn').forEach(button => {
            button.addEventListener('click', function() {
                const index = parseInt(this.getAttribute('data-image-index'), 10);
                if (!isNaN(index)) {
                    currentIndex = index;
                    updateModal();
                }
            });
        });

        // Keyboard navigation
        modal.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft' || e.key === 'Left') {
                e.preventDefault();
                showPrevious();
            } else if (e.key === 'ArrowRight' || e.key === 'Right') {
                e.preventDefault();
                showNext();
            }
        });

        // Reset to first image when modal is closed
        modal.addEventListener('hidden.bs.modal', function() {
            currentIndex = 0;
            updateModal();
        });

        // Initialize on modal show
        modal.addEventListener('shown.bs.modal', function() {
            updateModal();
            // Focus the modal for keyboard navigation
            modal.focus();
        });
    }

})();
