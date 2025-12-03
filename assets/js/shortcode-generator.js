/**
 * Shortcode Generator
 *
 * Generates shortcode based on form inputs
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

(function() {
    'use strict';

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initShortcodeGenerator);
    } else {
        initShortcodeGenerator();
    }

    function initShortcodeGenerator() {
        const showFiltersCheckbox = document.getElementById('sg_show_filters');
        const filterOptions = document.getElementById('sg_filter_options');
        const filterTitles = document.getElementById('sg_filter_titles');
        const resetButtonRow = document.getElementById('sg_reset_button_row');
        const generateBtn = document.getElementById('sg_generate');
        const outputRow = document.getElementById('sg_output_row');
        const outputTextarea = document.getElementById('sg_output');
        const copyBtn = document.getElementById('sg_copy');
        const copyFeedback = document.getElementById('sg_copy_feedback');

        if (!showFiltersCheckbox || !generateBtn) {
            return;
        }

        /**
         * Toggle filter-related fields
         */
        function toggleFilterFields() {
            const showFilters = showFiltersCheckbox.checked;
            if (filterOptions) filterOptions.style.display = showFilters ? '' : 'none';
            if (filterTitles) filterTitles.style.display = showFilters ? '' : 'none';
            if (resetButtonRow) resetButtonRow.style.display = showFilters ? '' : 'none';
        }

        /**
         * Generate shortcode from form inputs
         */
        function generateShortcode() {
            let shortcode = '[jpkcom_acf_references_list';
            const attributes = [];

            // Layout
            const layout = document.getElementById('sg_layout').value;
            if (layout && layout !== 'list') {
                attributes.push(`layout="${layout}"`);
            }

            // Limit
            const limit = document.getElementById('sg_limit').value;
            if (limit && limit !== '0') {
                attributes.push(`limit="${limit}"`);
            }

            // Sort
            const sort = document.getElementById('sg_sort').value;
            if (sort && sort !== 'DSC') {
                attributes.push(`sort="${sort}"`);
            }

            // Title
            const title = document.getElementById('sg_title').value;
            if (title) {
                attributes.push(`title="${escapeAttribute(title)}"`);
            }

            // CSS Class
            const cssClass = document.getElementById('sg_class').value;
            if (cssClass) {
                attributes.push(`class="${escapeAttribute(cssClass)}"`);
            }

            // Pre-filter: Type
            const type = document.getElementById('sg_type').value;
            if (type) {
                attributes.push(`type="${escapeAttribute(type)}"`);
            }

            // Pre-filter: Filter 1
            const filter1 = document.getElementById('sg_filter_1').value;
            if (filter1) {
                attributes.push(`filter_1="${escapeAttribute(filter1)}"`);
            }

            // Pre-filter: Filter 2
            const filter2 = document.getElementById('sg_filter_2').value;
            if (filter2) {
                attributes.push(`filter_2="${escapeAttribute(filter2)}"`);
            }

            // Pre-filter: Customer
            const customer = document.getElementById('sg_customer').value;
            if (customer) {
                attributes.push(`customer="${escapeAttribute(customer)}"`);
            }

            // Pre-filter: Location
            const location = document.getElementById('sg_location').value;
            if (location) {
                attributes.push(`location="${escapeAttribute(location)}"`);
            }

            // Show Filters
            const showFilters = document.getElementById('sg_show_filters').checked;
            if (showFilters) {
                attributes.push('show_filters="true"');

                // Which filters to show
                const showFilterCheckboxes = document.querySelectorAll('input[name="show_filter[]"]:checked');
                const showFilterValues = Array.from(showFilterCheckboxes).map(cb => cb.value);
                if (showFilterValues.length > 0) {
                    attributes.push(`show_filter="${showFilterValues.join(',')}"`);
                }

                // Filter titles
                const filterTitle0 = document.getElementById('sg_filter_title_0').value;
                if (filterTitle0) {
                    attributes.push(`filter_title_0="${escapeAttribute(filterTitle0)}"`);
                }

                const filterTitle1 = document.getElementById('sg_filter_title_1').value;
                if (filterTitle1) {
                    attributes.push(`filter_title_1="${escapeAttribute(filterTitle1)}"`);
                }

                const filterTitle2 = document.getElementById('sg_filter_title_2').value;
                if (filterTitle2) {
                    attributes.push(`filter_title_2="${escapeAttribute(filterTitle2)}"`);
                }

                // Reset button
                const resetButton = document.getElementById('sg_reset_button').checked;
                if (resetButton) {
                    attributes.push('reset_button="true"');
                }
            }

            // Build final shortcode
            if (attributes.length > 0) {
                shortcode += ' ' + attributes.join(' ');
            }
            shortcode += ']';

            return shortcode;
        }

        /**
         * Escape attribute values
         */
        function escapeAttribute(value) {
            return value
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        /**
         * Copy to clipboard
         */
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                // Use modern Clipboard API
                navigator.clipboard.writeText(text).then(function() {
                    showCopyFeedback();
                }).catch(function(err) {
                    console.error('Failed to copy:', err);
                    fallbackCopy(text);
                });
            } else {
                // Fallback for older browsers
                fallbackCopy(text);
            }
        }

        /**
         * Fallback copy method
         */
        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                showCopyFeedback();
            } catch (err) {
                console.error('Fallback copy failed:', err);
            }
            document.body.removeChild(textarea);
        }

        /**
         * Show copy feedback
         */
        function showCopyFeedback() {
            if (copyFeedback) {
                copyFeedback.style.display = 'inline';
                setTimeout(function() {
                    copyFeedback.style.display = 'none';
                }, 2000);
            }
        }

        // Event listeners
        showFiltersCheckbox.addEventListener('change', toggleFilterFields);

        generateBtn.addEventListener('click', function() {
            const shortcode = generateShortcode();
            outputTextarea.value = shortcode;
            outputRow.style.display = '';
            outputTextarea.select();
        });

        copyBtn.addEventListener('click', function() {
            const shortcode = outputTextarea.value;
            if (shortcode) {
                copyToClipboard(shortcode);
            }
        });

        // Initialize visibility
        toggleFilterFields();
    }

})();
