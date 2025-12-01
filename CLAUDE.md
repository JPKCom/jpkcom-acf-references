# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a WordPress plugin called **JPKCom ACF References** - a reference gallery system with filter functions built on Advanced Custom Fields Pro. It provides custom post types (references, locations, customers), custom taxonomies, and a complete template system for displaying reference projects, portfolios, and case studies.

**Requirements:**
- WordPress 6.8+
- PHP 8.3+
- Advanced Custom Fields Pro (required dependency)
- ACF Quick Edit Fields (required dependency)
- WPML (optional, for multilingual support via wpml-config.xml)

## Architecture

### Core Plugin Structure

The plugin uses a **modular file loader pattern** with override capabilities. The main file `jpkcom-acf-references.php` orchestrates loading via `jpkcom_acfreferences_locate_file()` which searches for files in this priority:

1. Child theme: `/wp-content/themes/your-child-theme/jpkcom-acf-references/`
2. Parent theme: `/wp-content/themes/your-theme/jpkcom-acf-references/`
3. MU plugin overrides: `/wp-content/mu-plugins/jpkcom-acf-references-overrides/`
4. Plugin itself: `/wp-content/plugins/jpkcom-acf-references/includes/`

This override system allows developers to customize any functional file without modifying the plugin.

### Custom Post Types

Three interconnected post types registered in `includes/acf-post_types.php`:

- **reference**: The main reference project (public, queryable)
- **reference_location**: Project locations (nested under references in admin)
- **reference_customer**: Customers/clients (nested under references in admin)

### Custom Taxonomies

Three hierarchical taxonomies registered in `includes/acf-taxonomies.php`:

- **reference-type**: Main categorization for references (e.g., "Web Development", "Design", "Consulting")
- **reference-filter-1**: First custom filter dimension (configurable for project-specific needs)
- **reference-filter-2**: Second custom filter dimension (configurable for project-specific needs)

All taxonomies support hierarchical organization and are available for filtering in both admin and frontend.

### Template System

Templates in `templates/` directory with debug versions in `debug-templates/` (loaded when `WP_DEBUG` is true).

**Template loading order** via `jpkcom_acf_references_locate_template()` in `includes/template-loader.php`:

1. Child theme: `/wp-content/themes/your-child-theme/jpkcom-acf-references/`
2. Parent theme: `/wp-content/themes/your-theme/jpkcom-acf-references/`
3. MU plugin: `/wp-content/mu-plugins/jpkcom-acf-references-overrides/templates/`
4. Plugin: `/wp-content/plugins/jpkcom-acf-references/templates/` (or `debug-templates/` if `WP_DEBUG`)

Key templates:
- `single-reference.php`, `single-reference_customer.php`, `single-reference_location.php`
- `archive-reference.php`, `archive-reference_customer.php`, `archive-reference_location.php`
- `partials/reference/*.php` - reusable reference components
- `partials/layout/*.php` - layout components (meta, pagination)
- `partials/archive/*.php` - archive-specific components
- `shortcodes/list.php`, `shortcodes/types.php`, `shortcodes/filter-1.php`, `shortcodes/filter-2.php` - shortcode templates

### ACF Field Configuration

All ACF field groups are registered programmatically in `includes/acf-field_groups.php` using `acf_add_local_field_group()`. This includes:

**Reference Fields:**
- `reference_url` - External reference URL (URL field)
- `reference_short_description` - Brief project description (Textarea)
- `reference_customer` - Link to customer post (Post Object)
- `reference_location` - Link to location post (Post Object)
- `reference_year` - Project year (Number field)
- `reference_image_gallery` - Project images (Gallery field)
- `reference_type` - Reference type taxonomy (Taxonomy field, checkboxes)
- `reference_filter_1` - First custom filter taxonomy (Taxonomy field, checkboxes)
- `reference_filter_2` - Second custom filter taxonomy (Taxonomy field, checkboxes)
- `reference_featured` - Featured/priority flag (True/False)
- `reference_expiry_date` - Optional expiry date (Date Picker)

**Location Fields:**
- `reference_location_place` - Location name (Text)
- `reference_location_zip` - Postal code (Text)
- `reference_location_street` - Street address (Text)
- `reference_location_region` - State/Region (Text)
- `reference_location_country` - Country name (Text)

**Customer Fields:**
- `reference_customer_url` - Customer website URL (URL field)
- `reference_customer_logo` - Customer logo image (Image field)

### Shortcodes

Registered in `includes/shortcodes.php`:

**`[jpkcom_acf_references_list]`** - Filtered reference list with attributes:
- `type` - CSV of reference type term IDs (e.g., "1,5")
- `customer` - CSV of customer post IDs
- `location` - CSV of location post IDs
- `limit` - Number of posts (default: all)
- `sort` - "ASC" or "DSC" (default: "DSC")
- `style` - Inline CSS
- `class` - CSS classes
- `title` - Section headline

**`[jpkcom_acf_references_types]`** - Display reference types taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

**`[jpkcom_acf_references_filter_1]`** - Display reference filter 1 taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

**`[jpkcom_acf_references_filter_2]`** - Display reference filter 2 taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

### Helper Functions

Key functions in `includes/helpers.php`:

- `jpkcom_render_acf_fields($post_type = '')` - Auto-renders all ACF fields with Bootstrap 5 markup and icon mapping
- `jpkcom_get_acf_field_label($field_name, $post_type = '')` - Returns human-readable field labels
- `jpkcom_human_readable_relative_date($timestamp)` - Converts timestamp to "Published X days ago"

Template loading:
- `jpkcom_acf_references_get_template_part($slug, $name = '')` - Load partial templates with full override support (similar to `get_template_part()`)

### Plugin Updates

Custom GitHub-based updater in `includes/class-plugin-updater.php` (namespace: `JPKComAcfReferencesGitUpdate`) provides secure, self-hosted updates:

**Security Features:**
- SHA256 checksum verification of downloaded packages (since v1.0.0)
- URL validation and sanitization using `wp_http_validate_url()`
- Race condition prevention with transient locking mechanism
- Comprehensive error logging in `WP_DEBUG` mode
- Backward compatibility with manifests without checksums

**Update Flow:**
1. Fetches manifest from: `https://jpkcom.github.io/jpkcom-acf-references/plugin_jpkcom-acf-references.json`
2. Caches manifest data with 24-hour TTL (transient)
3. Compares versions and displays update notice
4. Downloads plugin ZIP before installation
5. Verifies SHA256 checksum against manifest (via `verify_download_checksum()`)
6. Aborts installation with `WP_Error` if checksum fails
7. Proceeds with standard WordPress upgrade if verification passes

**Hooks Used:**
- `plugins_api` - Provides plugin info for "View Details" modal
- `site_transient_update_plugins` - Injects update availability
- `upgrader_pre_download` - Verifies checksum before installation
- `upgrader_process_complete` - Clears cache after successful update

**Manifest Generation:** Automated by `.github/workflows/release.yml` (see Release Process below)

## Development Workflow

### Making Code Changes

1. Edit PHP files directly in `includes/` or `templates/`
2. Test with `WP_DEBUG` enabled to use `debug-templates/` versions
3. ACF field changes should be made in `includes/acf-field_groups.php` (programmatic registration)

### Testing Template Changes

Enable `WP_DEBUG` in wp-config.php to load templates from `debug-templates/` instead of `templates/`:

```php
define('WP_DEBUG', true);
```

### Version Management

Version number appears in THREE locations and must be kept in sync:
1. `jpkcom-acf-references.php` - Plugin header (line 6)
2. `jpkcom-acf-references.php` - Updater initialization (line 68)
3. `README.md` - Multiple locations in header metadata

### Release Process

Releases are automated via GitHub Actions (`.github/workflows/release.yml`):

1. Create a new Git tag: `git tag v1.x.x && git push --tags`
2. Create GitHub release from the tag on GitHub
3. Workflow automatically (triggered by `release: [published]` event):
   - **Extracts metadata** from `README.md` using Pandoc and bash
   - **Builds plugin ZIP** excluding git files, CLAUDE.md, and workflow files
   - **Generates SHA256 checksum** of the ZIP file (via `sha256sum`)
   - **Creates `.sha256` file** for manual verification
   - **Uploads both ZIP and `.sha256`** to the GitHub release
   - **Generates manifest JSON** (Python script) with:
     - Plugin metadata extracted from README.md
     - `download_url` pointing to GitHub release ZIP
     - `checksum_sha256` field containing the SHA256 hash
     - HTML sections (description, installation, changelog, FAQ) converted from Markdown
   - **Deploys to gh-pages** branch (manifest, HTML docs, assets)

**Key Workflow Steps:**
- Step 6: `Create plugin ZIP` - Builds release archive
- Step 6.1: `Generate SHA256 checksum` - Creates hash for security verification
- Step 7: `Upload ZIP and checksum` - Attaches files to release
- Step 8: `Generate plugin manifest JSON` - Python script builds manifest with checksum
- Step 10: `Deploy to gh-pages` - Publishes manifest and docs

**Important:** The SHA256 checksum in the manifest is automatically verified during plugin updates via `includes/class-plugin-updater.php`

### Adding Custom Filters

The plugin provides several filter hooks for customization:

- `jpkcom_acfreferences_file_paths` - Modify functional file search paths
- `jpkcom_acf_references_template_paths` - Modify template search paths
- `jpkcom_acf_references_final_template` - Last-chance template override
- `jpkcom_acf_references_list_query_args` - Modify shortcode query arguments

### WPML/Translation Support

Multilingual configuration in `wpml-config.xml` provides comprehensive WPML integration:

**Post Types & Taxonomies:**
- `reference` - Marked for translation (`translate="1"`)
- `reference_location` - Translate + display as translated (`translate="1" display-as-translated="1"`)
- `reference_customer` - Translate + display as translated
- `reference-type` - Custom taxonomy marked for translation
- `reference-filter-1` - Custom taxonomy marked for translation
- `reference-filter-2` - Custom taxonomy marked for translation

**ACF Field Translation Strategy:**

Three action types control how fields are handled across languages:

1. **`action="translate"`** - Content differs per language:
   - `reference_short_description`
   - `reference_location_place`
   - `reference_location_street`
   - `reference_location_region`
   - `reference_location_country`

2. **`action="copy-once"`** - Copied once, then independent:
   - `reference_url`, `reference_customer`, `reference_location`
   - `reference_year`, `reference_image_gallery`
   - `reference_type`, `reference_filter_1`, `reference_filter_2`
   - `reference_featured`, `reference_expiry_date`
   - All customer fields (`reference_customer_url`, `reference_customer_logo`)
   - Location zip code (`reference_location_zip`)

3. **`action="copy"`** - Kept in sync across translations:
   - (Not used in this plugin - no fields require permanent synchronization)

**ACF Internal Fields (Prefixed with `_`):**

WPML requires special handling of ACF's internal meta fields:

- **Standard fields:** `action="ignore"` - These store field keys, not content (e.g., `_reference_type`, `_reference_url`)
- All underscore-prefixed fields in `wpml-config.xml` are set to `ignore` as they contain ACF metadata, not user content

**Translation Files:**
- Located in `languages/` directory
- Format: `.l10n.php` (WordPress 6.8+ format)
- Text domain: `jpkcom-acf-references`

## Common Patterns

### Adding a New Template Partial

1. Create file in `templates/partials/reference/`
2. Use `jpkcom_acf_references_get_template_part('partials/reference/filename')` to load
3. Optionally create debug version in `debug-templates/partials/reference/`

### Querying References with Meta Filters

References support complex meta queries. Key meta fields:
- `reference_featured` - Sorting priority (numeric)
- `reference_expiry_date` - Date field (Y-m-d format)
- `reference_type` - Serialized array (use LIKE '"VALUE"')
- `reference_customer` - Serialized array of post IDs
- `reference_location` - Serialized array of post IDs
- `reference_year` - Numeric year value

Example query:
```php
$args = [
    'post_type' => 'reference',
    'meta_query' => [
        'relation' => 'AND',
        [
            'key' => 'reference_featured',
            'value' => '1',
            'compare' => '='
        ],
        [
            'key' => 'reference_year',
            'value' => 2024,
            'compare' => '>=',
            'type' => 'NUMERIC'
        ]
    ],
    'orderby' => 'meta_value_num',
    'meta_key' => 'reference_featured',
    'order' => 'DESC'
];
```

### Overriding Plugin Files (for end users)

Developers can override files without modifying plugin code:

**Templates**: Copy to theme directory:
```
/wp-content/themes/your-theme/jpkcom-acf-references/single-reference.php
```

**Functional libraries**: Use filter:
```php
add_filter('jpkcom_acfreferences_file_paths', function($paths, $filename) {
    array_unshift($paths, WP_CONTENT_DIR . '/custom-overrides/' . $filename);
    return $paths;
}, 10, 2);
```

**Template paths**: Use filter:
```php
add_filter('jpkcom_acf_references_template_paths', function($paths, $template_name) {
    array_unshift($paths, WP_CONTENT_DIR . '/custom-templates/jpkcom-acf-references/' . $template_name);
    return $paths;
}, 10, 2);
```

**Last-chance template override**:
```php
add_filter('jpkcom_acf_references_final_template', function($template) {
    if (is_singular('reference')) {
        return WP_CONTENT_DIR . '/special/single-reference-custom.php';
    }
    return $template;
});
```

### Working with Image Galleries

Reference image galleries use ACF's native Gallery field:

```php
// Get gallery images for current reference
$gallery_images = get_field('reference_image_gallery');

if ($gallery_images) {
    foreach ($gallery_images as $image) {
        echo '<img src="' . esc_url($image['sizes']['large']) . '" alt="' . esc_attr($image['alt']) . '">';
    }
}
```

Gallery images include all standard WordPress image sizes and metadata (title, alt, caption, description).

## Code Style

- Uses PHP 8.3 features (named parameters, type declarations)
- WordPress Coding Standards
- Text domain: `jpkcom-acf-references`
- All strings must be translatable with `__()`, `esc_html__()`, etc.
- Bootstrap 5 markup in templates
- Constants prefixed with `JPKCOM_ACFREFERENCES_`
- Functions prefixed with `jpkcom_acfreferences_` (file loading) or `jpkcom_acf_references_` (templates/shortcodes)

## Plugin Constants

Defined in `jpkcom-acf-references.php`:

- `JPKCOM_ACFREFERENCES_VERSION` - Plugin version (currently `1.0.0`)
- `JPKCOM_ACFREFERENCES_BASENAME` - Plugin basename for WordPress hooks
- `JPKCOM_ACFREFERENCES_PLUGIN_PATH` - Absolute path to plugin directory
- `JPKCOM_ACFREFERENCES_PLUGIN_URL` - URL to plugin directory

## Key Functions Reference

### File Loading
- `jpkcom_acfreferences_locate_file($filename)` - Locate functional files with override support
- `jpkcom_acfreferences_textdomain()` - Load translation files

### Template Loading
- `jpkcom_acf_references_locate_template($template_name)` - Locate template files with override support
- `jpkcom_acf_references_get_template_part($slug, $name)` - Load template partials (similar to WordPress `get_template_part()`)

### Field Rendering
- `jpkcom_render_acf_fields($post_type)` - Auto-render all ACF fields with Bootstrap 5 markup
- `jpkcom_get_acf_field_label($field_name, $post_type)` - Get human-readable field label

### Utilities
- `jpkcom_human_readable_relative_date($timestamp)` - Format relative dates ("Published 3 days ago")

## Admin Organization

The plugin organizes the WordPress admin menu as follows:

**References** (main menu item)
- All References
- Add New Reference
- Reference Types (taxonomy)
- Reference Filter 1 (taxonomy)
- Reference Filter 2 (taxonomy)
- Locations (nested)
- Customers (nested)

Locations and Customers appear as sub-items under the References menu for better organization.

## Frontend Features

### Archive Pages
- `/references/` - Main reference archive with filtering
- `/references/reference-type/{slug}/` - Filtered by type
- `/reference-location/{slug}/` - Location-specific references
- `/reference-customer/{slug}/` - Customer-specific references

### Single Pages
- `/references/{slug}/` - Individual reference detail page
- `/reference-location/{slug}/` - Location detail with related references
- `/reference-customer/{slug}/` - Customer detail with related references

### Shortcode Display
Use shortcodes anywhere (posts, pages, widgets):
- `[jpkcom_acf_references_list]` - Filterable reference grid
- `[jpkcom_acf_references_types]` - Interactive type filter

## Performance Considerations

- ACF fields are registered programmatically (faster than JSON import)
- Template caching is handled by WordPress template hierarchy
- Gallery images should be properly sized (use WordPress image sizes)
- Consider implementing lazy loading for image galleries
- Use transients for expensive queries in custom implementations

## Debugging

Enable WordPress debug mode in `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

This will:
- Load templates from `debug-templates/` instead of `templates/`
- Enable detailed error logging for the plugin updater
- Log PHP errors to `/wp-content/debug.log`

## Security Best Practices

- All output is properly escaped (`esc_html()`, `esc_url()`, `esc_attr()`)
- User input is sanitized before database queries
- Nonces are used for form submissions (if applicable)
- File paths are validated before inclusion
- Database queries use WordPress prepared statements
- Plugin updater verifies SHA256 checksums before installation

## Common Troubleshooting

**Issue: References not displaying**
- Verify ACF Pro is installed and activated
- Check permalink settings (resave permalinks in Settings > Permalinks)
- Ensure references are published (not draft)

**Issue: Images not showing in gallery**
- Check that images are uploaded via the ACF Gallery field
- Verify image files exist in the media library
- Ensure proper image size generation (regenerate thumbnails if needed)

**Issue: Shortcode not working**
- Verify shortcode syntax is correct
- Check that template files exist in `templates/shortcodes/`
- Enable `WP_DEBUG` to see any PHP errors

**Issue: Taxonomies not filtering**
- Ensure taxonomy terms are assigned to references
- Check that taxonomy slugs match in queries
- Verify WPML translation settings if using multilingual

**Issue: Template overrides not loading**
- Check file path matches exactly (case-sensitive)
- Ensure override file has correct filename
- Clear any caching plugins
- Verify directory permissions (755 for directories, 644 for files)
