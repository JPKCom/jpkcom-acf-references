/**
 * JPKCom ACF References - Client-side List Filtering
 *
 * Handles client-side filtering of reference lists with URL hash-bang support.
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

(function() {
    'use strict';

    /**
     * Reference List Filter Controller
     */
    class ReferenceListFilter {

        /**
         * Constructor
         *
         * @param {HTMLElement} container - The list container element
         */
        constructor(container) {
            this.container = container;
            this.listId = container.id;
            this.items = container.querySelectorAll('.jpkcom-acf-ref-item');
            this.filterButtons = container.querySelectorAll('.jpkcom-acf-ref-filter-btn');
            this.resetButtons = container.querySelectorAll('.jpkcom-acf-ref-filter-reset');
            this.optionButtons = container.querySelectorAll('.jpkcom-acf-ref-filter-option');

            // Active filters state: { 'reference-type': '12', 'reference-filter-1': '5' }
            this.activeFilters = {};

            this.init();
        }

        /**
         * Initialize the filter
         */
        init() {
            this.attachEventListeners();
            this.parseHashAndApplyFilters();

            // Listen for hash changes
            window.addEventListener('hashchange', () => {
                this.parseHashAndApplyFilters();
            });

            // Listen for keyboard navigation
            this.setupKeyboardNavigation();
        }

        /**
         * Attach event listeners to buttons
         */
        attachEventListeners() {
            // Filter option buttons
            this.optionButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const filterId = button.dataset.filterId;
                    const termId = button.dataset.termId;
                    const termName = button.dataset.termName;

                    this.setFilter(filterId, termId, termName);
                });
            });

            // Reset buttons
            this.resetButtons.forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const filterId = button.dataset.filterId;

                    this.clearFilter(filterId);
                });
            });
        }

        /**
         * Setup keyboard navigation for accessibility
         */
        setupKeyboardNavigation() {
            // Handle Enter and Space on dropdown items
            this.optionButtons.forEach(button => {
                button.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        button.click();
                    }
                });
            });

            this.resetButtons.forEach(button => {
                button.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        button.click();
                    }
                });
            });
        }

        /**
         * Set a filter
         *
         * @param {string} filterId - Filter identifier (e.g., 'reference-type')
         * @param {string} termId - Term ID to filter by
         * @param {string} termName - Term name for display
         */
        setFilter(filterId, termId, termName) {
            this.activeFilters[filterId] = {
                id: termId,
                name: termName
            };

            this.updateButtonLabel(filterId, termName);
            this.updateHash();
            this.applyFilters();
        }

        /**
         * Clear a specific filter
         *
         * @param {string} filterId - Filter identifier to clear
         */
        clearFilter(filterId) {
            delete this.activeFilters[filterId];

            // Reset button label to default
            const button = this.container.querySelector(
                `.jpkcom-acf-ref-filter-btn[data-filter-id="${filterId}"]`
            );

            if (button) {
                const defaultLabel = button.dataset.defaultLabel;
                this.updateButtonLabel(filterId, defaultLabel);
            }

            this.updateHash();
            this.applyFilters();
        }

        /**
         * Update filter button label
         *
         * @param {string} filterId - Filter identifier
         * @param {string} label - New label text
         */
        updateButtonLabel(filterId, label) {
            const button = this.container.querySelector(
                `.jpkcom-acf-ref-filter-btn[data-filter-id="${filterId}"]`
            );

            if (button) {
                const labelSpan = button.querySelector('.filter-label');
                if (labelSpan) {
                    labelSpan.textContent = label;
                }

                // Update aria-label
                const defaultLabel = button.dataset.defaultLabel;
                if (label === defaultLabel) {
                    button.setAttribute('aria-label', `Filter by ${label}`);
                } else {
                    button.setAttribute('aria-label', `Filtered by ${label}. Click to change.`);
                }
            }
        }

        /**
         * Apply all active filters to list items
         */
        applyFilters() {
            const hasActiveFilters = Object.keys(this.activeFilters).length > 0;

            this.items.forEach(item => {
                let shouldShow = true;

                if (hasActiveFilters) {
                    // Check each active filter
                    for (const [filterId, filterData] of Object.entries(this.activeFilters)) {
                        const itemTermIds = item.dataset[this.camelCase(filterId)];

                        if (!itemTermIds) {
                            shouldShow = false;
                            break;
                        }

                        // Check if item has this term ID
                        const termIdArray = itemTermIds.split(',').map(id => id.trim());
                        if (!termIdArray.includes(filterData.id)) {
                            shouldShow = false;
                            break;
                        }
                    }
                }

                // Show or hide item with transition
                if (shouldShow) {
                    item.style.display = '';
                    item.setAttribute('aria-hidden', 'false');
                } else {
                    item.style.display = 'none';
                    item.setAttribute('aria-hidden', 'true');
                }
            });

            // Announce to screen readers
            this.announceFilterResults();
        }

        /**
         * Announce filter results to screen readers
         */
        announceFilterResults() {
            const visibleCount = Array.from(this.items).filter(
                item => item.style.display !== 'none'
            ).length;

            // Create or update live region
            let liveRegion = this.container.querySelector('.filter-live-region');
            if (!liveRegion) {
                liveRegion = document.createElement('div');
                liveRegion.className = 'filter-live-region visually-hidden';
                liveRegion.setAttribute('aria-live', 'polite');
                liveRegion.setAttribute('aria-atomic', 'true');
                this.container.appendChild(liveRegion);
            }

            const message = visibleCount === 1
                ? `${visibleCount} reference found`
                : `${visibleCount} references found`;

            liveRegion.textContent = message;
        }

        /**
         * Parse URL hash and apply filters
         */
        parseHashAndApplyFilters() {
            const hash = window.location.hash;

            // Check if hash starts with #! (hash-bang)
            if (!hash || !hash.startsWith('#!')) {
                return;
            }

            // Remove #! prefix
            const hashParams = hash.substring(2);

            // Parse parameters: listId:filterId=termId,termName;filterId2=termId2,termName2
            const parts = hashParams.split(':');

            if (parts.length < 2) {
                return;
            }

            const targetListId = parts[0];

            // Only apply if this is the target list
            if (targetListId !== this.listId) {
                return;
            }

            // Clear current filters
            this.activeFilters = {};

            // Parse filter parameters
            const filterParams = parts[1].split(';');

            filterParams.forEach(param => {
                const [filterId, valueStr] = param.split('=');

                if (filterId && valueStr) {
                    const [termId, termName] = valueStr.split(',');

                    if (termId && termName) {
                        this.activeFilters[filterId] = {
                            id: decodeURIComponent(termId),
                            name: decodeURIComponent(termName)
                        };

                        this.updateButtonLabel(filterId, decodeURIComponent(termName));
                    }
                }
            });

            this.applyFilters();
        }

        /**
         * Update URL hash with current filters
         */
        updateHash() {
            const filterParts = [];

            for (const [filterId, filterData] of Object.entries(this.activeFilters)) {
                const encodedName = encodeURIComponent(filterData.name);
                filterParts.push(`${filterId}=${filterData.id},${encodedName}`);
            }

            if (filterParts.length > 0) {
                const newHash = `#!${this.listId}:${filterParts.join(';')}`;

                // Update hash without triggering hashchange event
                if (window.history.replaceState) {
                    window.history.replaceState(null, '', newHash);
                } else {
                    window.location.hash = newHash;
                }
            } else {
                // Clear hash if no filters active
                if (window.history.replaceState) {
                    window.history.replaceState(null, '', window.location.pathname);
                } else {
                    window.location.hash = '';
                }
            }
        }

        /**
         * Convert kebab-case to camelCase for dataset access
         *
         * @param {string} str - String to convert
         * @return {string} camelCase string
         */
        camelCase(str) {
            return str.replace(/-([a-z])/g, (g) => g[1].toUpperCase());
        }
    }

    /**
     * Initialize all reference list filters on page
     */
    function initReferenceListFilters() {
        const containers = document.querySelectorAll('.jpkcom-acf-references--list');

        containers.forEach(container => {
            // Only initialize if filters are present
            if (container.querySelector('.jpkcom-acf-ref-filters')) {
                new ReferenceListFilter(container);
            }
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReferenceListFilters);
    } else {
        initReferenceListFilters();
    }

})();
