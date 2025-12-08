# JPKCom ACF References

**Plugin Name:** JPKCom ACF References  
**Plugin URI:** https://github.com/JPKCom/jpkcom-acf-references  
**Description:** Reference gallery with filter function plugin for ACF  
**Version:** 1.0.1  
**Author:** Jean Pierre Kolb <jpk@jpkc.com>  
**Author URI:** https://www.jpkc.com/  
**Contributors:** JPKCom  
**Tags:** ACF, Fields, CPT, CTT, Taxonomy, Images  
**Requires Plugins:** advanced-custom-fields-pro, acf-quickedit-fields  
**Requires at least:** 6.8  
**Tested up to:** 6.9  
**Requires PHP:** 8.3  
**Network:** true  
**Stable tag:** 1.0.1  
**License:** GPL-2.0+  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.txt  
**Text Domain:** jpkcom-acf-references  
**Domain Path:** /languages

A plugin to provide a reference gallery with filter function tool for ACF Pro.


## Description

**JPKCom ACF References** is a reference gallery system with filter functions built on Advanced Custom Fields Pro. This plugin provides a complete solution for creating, managing, and displaying reference projects on your WordPress website with powerful features for portfolios, case studies, and project showcases.

### Key Features

- **Three Custom Post Types**: References, Locations, and Customers with hierarchical organization
- **Image Galleries**: Full support for image galleries with lightbox modal and thumbnail grid display
- **Interactive Filtering**: JavaScript-based client-side filtering with animated transitions respecting `prefers-reduced-motion`
- **Multiple Layouts**: Choose between list, card grid, or image overlay layouts for references
- **Admin Tools**: Built-in shortcode generator and settings page for archive redirect configuration
- **Advanced Querying**: Filter references by type, location, customer, and custom filter taxonomies
- **Multilingual Ready**: Full WPML support with translation-aware field configuration (German translations included)
- **Template Override System**: Customize any template via child theme, parent theme, or mu-plugins
- **Developer-Friendly**: Helper functions, filters, and shortcodes for easy customization
- **Bootstrap 5 Ready**: Pre-styled templates with modern responsive markup
- **Automatic Updates**: Secure GitHub-based plugin updates with SHA256 checksum verification
- **Accessibility First**: ARIA labels, keyboard navigation, and screen reader support throughout

### Requirements

The following plugins are **required** for this plugin to work:

- [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/) (v6.0+)
- [ACF Quick Edit Fields](https://wordpress.org/plugins/acf-quickedit-fields/) (for inline editing)

**Optional:**
- [WPML](https://wpml.org/) for multilingual reference projects

### What's Included

- **Custom Fields** (`includes/acf-field_groups.php`) - Programmatically registered ACF field groups for references, locations, and customers
- **Custom Post Types** (`includes/acf-post_types.php`) - Reference, Location, and Customer post types with proper admin organization
- **Custom Taxonomies** (`includes/acf-taxonomies.php`) - Three taxonomies: reference-type, reference-filter-1, reference-filter-2
- **Template System** (`templates/`, `debug-templates/`) - Complete set of single and archive templates with override support
- **Shortcode Templates** (`templates/shortcodes/`) - List, types, and filter display templates with layout partials (list-items.php, list-cards.php, list-images.php)
- **Shortcodes** (`includes/shortcodes.php`) - Display filtered reference lists with interactive filtering, taxonomy displays
- **Helper Functions** (`includes/helpers.php`) - Utility functions for rendering fields and formatting dates
- **Admin Pages** (`includes/admin-pages.php`) - Shortcode generator and settings page for archive redirect
- **JavaScript Filtering** (`assets/js/reference-list-filter.js`) - Client-side filtering with animated transitions (vanilla JS, no jQuery)
- **Image Gallery Modal** (`assets/js/gallery-modal.js`) - Lightbox with keyboard navigation and accessibility support
- **CSS Animations** (`assets/css/reference-styles.css`) - Smooth fade-in/fade-out effects with `prefers-reduced-motion` support
- **Translation Files** (`languages/`) - German translations included (de_DE, de_DE_formal) with .l10n.php format
- **Automatic Updates** (`includes/class-plugin-updater.php`) - GitHub-based update system with SHA256 checksum verification

### Get Template Parts

```php
// Native WordPress:
get_template_part( 'jpkcom-acf-references/partials/reference/customer' );
// Plugin:
jpkcom_acf_references_get_template_part( 'partials/reference/customer' );
```

### Shortcodes

All shortcode attributes are optional.

#### Reference list with interactive filter functions:
```
[jpkcom_acf_references_list type="1,5" customer="6,8" location="1,3,7,11" limit="10" sort="DSC" style="background:transparent;" class="mb-5" title="References Headline"]
```

**Advanced filtering with interactive dropdowns:**
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1,2" reset_button="true" filter_title_0="Project Type" filter_title_1="Category" filter_title_2="Technology" layout="cards" limit="20"]
```

**Attributes:**
- `type`, `filter_1`, `filter_2` - CSV of taxonomy term IDs for filtering
- `customer`, `location` - CSV of post IDs for filtering
- `limit` - Number of posts to display (default: all)
- `sort` - "ASC" or "DSC" (default: "DSC")
- `show_filters` - Enable interactive filter dropdowns (true/false)
- `show_filter` - CSV of filter indexes to display: 0=type, 1=filter_1, 2=filter_2 (e.g., "0,1")
- `reset_button` - Show "Reset all filters" button (true/false)
- `filter_title_0`, `filter_title_1`, `filter_title_2` - Custom labels for filter dropdowns
- `layout` - Display layout: "list" (default), "cards", or "images" (overlay cards)
- `style`, `class`, `title` - Styling and heading options

#### List of reference types displayed as `<details>` tags:
```
[jpkcom_acf_references_types id="3,7,21" style="background:transparent;" class="mb-5" title="Types Headline"]
```

#### Custom filter taxonomies:
```
[jpkcom_acf_references_filter_1 id="1,5" class="mb-3" title="Filter 1 Headline"]
[jpkcom_acf_references_filter_2 id="2,8" class="mb-3" title="Filter 2 Headline"]
```

### Helper functions

#### Renders all ACF fields of a post with Bootstrap 5 markup.

`@param string $post_type` Optional post type for field group query. If empty, 'current_post_type' is used.

```php
jpkcom_render_acf_fields();
```

## FAQ

### Why do I need Advanced Custom Fields Pro?

This plugin relies on ACF Pro's powerful field group system to provide flexible reference data management. ACF Pro offers advanced field types (repeaters, flexible content, groups, galleries) that are essential for complex reference projects with image galleries, multiple locations, and rich content layouts.

### How do I create my first reference?

1. After activation, go to **References → Add New** in your WordPress admin
2. Enter the reference title and description
3. Fill in the ACF fields: reference type, location, customer, gallery, etc.
4. Add reference types using the taxonomy on the right
5. Upload images to the gallery
6. Publish the reference

The reference will automatically appear in your reference archive.

### How do I display references on my website?

**Option 1: Use the shortcode (Simple)**
```
[jpkcom_acf_references_list limit="10" sort="DSC"]
```

**Option 2: Use the shortcode with interactive filters**
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1" reset_button="true" layout="cards"]
```
This displays a filterable list with dropdown controls that filter references instantly without page reload.

**Option 3: Navigate to the archive**
Visit `/references/` on your site to see all published references.

**Option 4: Create a custom template**
Use `WP_Query` with `post_type => 'reference'` to build custom reference displays.

### Is this plugin compatible with WPML?

Yes! The plugin includes full WPML support via `wpml-config.xml`. References, locations, customers, and taxonomies can all be translated. Fields are configured with appropriate translation strategies (translate, copy, or copy-once) for optimal multilingual workflow.

### How do I customize the reference templates?

You have three options:

**Option 1: Child Theme Override** (Recommended)
Copy templates from `plugins/jpkcom-acf-references/templates/` to `your-child-theme/jpkcom-acf-references/` and customize them.

**Option 2: Parent Theme Override**
Copy templates to `your-theme/jpkcom-acf-references/` (works if no child theme is active).

**Option 3: MU-Plugin Override**
Copy templates to `mu-plugins/jpkcom-acf-references-overrides/templates/` for site-wide customization.

### How to overwrite functional libraries?

```php
/**
 * Add new path for overwrites of functional libraries
 */
add_filter( 'jpkcom_acfreferences_file_paths', function( $paths, $filename ) {
    array_unshift( $paths, WP_CONTENT_DIR . '/custom-overrides/' . $filename );
    return $paths;
}, 10, 2 );
```

### How to overwrite template paths programmatically?

```php
/**
 * Add a new path, for example from the child theme or custom directory
 */
add_filter( 'jpkcom_acf_references_template_paths', function( $paths, $template_name ) {
    array_unshift( $paths, WP_CONTENT_DIR . '/custom-templates/jpkcom-acf-references/' . $template_name );
    return $paths;
}, 10, 2 );
```

```php
/**
 * Last chance to dynamically overwrite template path
 */
add_filter( 'jpkcom_acf_references_final_template', function( $template ) {
    if ( is_singular( 'reference' ) ) {
        return WP_CONTENT_DIR . '/special/single-reference-custom.php';
    }
    return $template;
});
```

### How do plugin updates work?

The plugin uses a secure GitHub-based update system. When a new version is released:

1. WordPress checks `https://jpkcom.github.io/jpkcom-acf-references/plugin_jpkcom-acf-references.json` for updates
2. Update notifications appear in your WordPress admin (Plugins page and Updates page)
3. When you click "Update Now", WordPress downloads the plugin ZIP from GitHub
4. The download is verified using SHA256 checksum for security
5. If the checksum matches, the update proceeds automatically

You can also download releases manually from the [GitHub repository](https://github.com/JPKCom/jpkcom-acf-references/releases).

### Can I filter references by location or customer?

Yes! Use the shortcode attributes:

```
[jpkcom_acf_references_list location="1,3,7" customer="6,8" type="1,5"]
```

Location and customer values are post IDs. You can find them in the admin when editing locations or customers (look at the URL: `post=123`).

### What are reference types and custom filters?

The plugin provides three taxonomy systems for organizing references:

**Reference Type** (reference-type) - Main categorization:
- Project categories: "Web Development", "Design", "Consulting"
- Industries: "Healthcare", "Finance", "Education"
- Technologies: "WordPress", "React", "PHP"

**Filter 1 & Filter 2** (reference-filter-1, reference-filter-2) - Custom dimensions:
- Configure these for your specific needs (e.g., "Project Size", "Technology Stack", "Service Type")
- Both support hierarchical organization
- Can be displayed and filtered independently

Display them with shortcodes:
```
[jpkcom_acf_references_types]
[jpkcom_acf_references_filter_1]
[jpkcom_acf_references_filter_2]
```

### How does the interactive filtering work?

The interactive filter system provides instant client-side filtering without page reloads:

**Features:**
- Dropdown controls for each taxonomy (type, filter 1, filter 2)
- Multiple selections per filter (checkboxes in dropdown)
- "OR" logic within a filter (show if ANY selected term matches)
- "AND" logic between filters (show only if ALL filters match)
- "Reset all filters" button to clear selections
- "No results" message when no references match
- Works with both list and card layouts

**Example usage:**
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1,2" reset_button="true" filter_title_0="Type" filter_title_1="Category" filter_title_2="Tech" layout="cards"]
```

**How it works technically:**
- All references load on initial page load
- Each reference has `data-*` attributes with taxonomy term IDs
- JavaScript shows/hides items based on selected filters
- No AJAX calls needed - instant filtering
- Accessible with ARIA labels and keyboard navigation

### What's the difference between the three layouts?

The `layout` attribute controls how references are displayed:

**List Layout** (`layout="list"`, default):
- Compact view with title, excerpt, and metadata
- Uses `templates/shortcodes/partials/list-items.php`
- Good for text-heavy reference lists
- Smaller thumbnails or no images
- Best for dense content with lots of references

**Cards Layout** (`layout="cards"`):
- Grid-based card display with larger images (16:9 format)
- Uses `templates/shortcodes/partials/list-cards.php`
- Better for visual portfolios
- Shows featured image, title, type, customer, and location
- Responsive grid (adjusts to screen size)
- Hover zoom effect on images (respects `prefers-reduced-motion`)

**Images Layout** (`layout="images"`):
- Full-bleed image overlay cards (4:3 format)
- Uses `templates/shortcodes/partials/list-images.php`
- Best for image-heavy portfolios and galleries
- Title and button overlaid on image
- No borders, no gaps between cards
- Dramatic hover zoom effect (respects `prefers-reduced-motion`)

All layouts support:
- Featured references (special CSS class)
- All filter controls with smooth animated transitions
- Custom styling via `class` and `style` attributes
- Accessibility features (ARIA labels, keyboard navigation)

### How do I use the shortcode generator?

Navigate to **References → Shortcodes** in your WordPress admin to access the interactive shortcode generator:

**Features:**
- Visual form with all available options
- Live shortcode preview
- One-click copy to clipboard
- Explanations for each attribute
- Example values for all fields

**Steps:**
1. Select your desired layout (list, cards, or images)
2. Set number of references to display
3. Choose sort order (ascending or descending)
4. Enable filters if needed and select which ones to show
5. Add pre-filtering by type, filter 1, filter 2, customer, or location (comma-separated IDs)
6. Click "Generate Shortcode" to see the result
7. Click "Copy to Clipboard" to copy the shortcode
8. Paste it into any page or post

### How do I disable the reference archive page?

If you don't want visitors to access `/references/`, you can redirect them:

1. Go to **References → Options** in WordPress admin
2. Check **"Disable Reference Archive"**
3. Optionally enter a custom redirect URL (defaults to homepage)
4. Click **"Save Changes"**

When enabled:
- Visitors accessing `/references/` are redirected (HTTP 307)
- Single reference pages remain accessible at `/references/post-slug/`
- Useful if you only want references displayed via shortcodes

### How do image galleries work?

References can have image galleries with a professional lightbox modal:

**Features:**
- Thumbnail grid (200×200px square thumbnails)
- Lightbox modal on click (1400px width images)
- Previous/Next navigation buttons
- Keyboard navigation (arrow keys, Escape to close)
- Image counter (e.g., "Image 3 of 8")
- Responsive and accessible
- Gallery appears on single reference pages automatically

**To add a gallery:**
1. Edit a reference in WordPress admin
2. Scroll to the "Image Gallery" field (ACF Gallery field)
3. Click "Add to gallery" and select/upload images
4. Drag to reorder images
5. Save the reference

The gallery will automatically display on the single reference page with thumbnails and lightbox functionality.

### How do filter animations work?

The plugin includes smooth CSS animations when filtering references:

**Animation behavior:**
- **Fade-out**: Items slide up slightly and fade to transparent (0.3s)
- **Fade-in**: Items slide down and fade to visible (0.3s)
- **Respects accessibility**: Animations are disabled if user has `prefers-reduced-motion` enabled
- **No jQuery needed**: Pure CSS transitions with JavaScript state management

**For users with motion preferences:**
- `prefers-reduced-motion: no-preference` → Smooth animations
- `prefers-reduced-motion: reduce` → Instant show/hide (no animation)

This ensures the filtering is both visually engaging and accessible to all users.

## Installation

### Prerequisites

Before installing this plugin, ensure you have:
- WordPress 6.8 or higher
- PHP 8.3 or higher
- [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/) installed and activated
- [ACF Quick Edit Fields](https://wordpress.org/plugins/acf-quickedit-fields/) installed and activated

### Method 1: Upload via WordPress Admin (Recommended)

1. Download the latest release ZIP file from the [GitHub Releases page](https://github.com/JPKCom/jpkcom-acf-references/releases)
2. In your WordPress admin panel, navigate to **Plugins → Add New**
3. Click the **Upload Plugin** button at the top of the page
4. Click **Choose File** and select the downloaded `jpkcom-acf-references.zip` file
5. Click **Install Now** and wait for the upload to complete
6. Click **Activate Plugin** to enable the plugin immediately

### Method 2: Manual Installation via FTP/SFTP

1. Download the latest release ZIP file from the [GitHub Releases page](https://github.com/JPKCom/jpkcom-acf-references/releases)
2. Extract the ZIP file on your local computer
3. Using an FTP/SFTP client, upload the extracted `jpkcom-acf-references` folder to `/wp-content/plugins/`
4. In your WordPress admin panel, navigate to **Plugins**
5. Find "JPKCom ACF References" in the list and click **Activate**

### Method 3: GitHub Clone (For Developers)

```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/JPKCom/jpkcom-acf-references.git
```

Then activate the plugin in the WordPress admin panel.

### Post-Installation Steps

1. **Verify Dependencies**: Go to **Plugins** and ensure ACF Pro and ACF Quick Edit Fields are active
2. **Check Custom Post Types**: You should now see **References**, **Locations**, and **Customers** in your admin menu
3. **Explore Admin Pages**:
   - Visit **References → Shortcodes** to use the shortcode generator
   - Visit **References → Options** to configure archive redirect settings
4. **Create Test Content**:
   - Create a location: **Locations → Add New**
   - Create a customer: **Customers → Add New**
   - Create a reference: **References → Add New** (assign the location and customer, add images to gallery)
5. **View Frontend**: Visit `/references/` on your site to see the reference archive
6. **Test Shortcode**: Use the shortcode generator to create a filtered list and paste it into a test page
7. **Add to Navigation** (Optional): Add the reference archive to your site menu via **Appearance → Menus**

### Automatic Updates

Once installed, the plugin will automatically check for updates from GitHub. Update notifications will appear in:
- **Dashboard → Updates**
- **Plugins** page (update notice below plugin name)

Simply click **Update Now** to install the latest version securely with SHA256 checksum verification.

### Multisite Installation

This plugin is **network-compatible**. To install on a multisite network:

1. Follow Method 1 or 2 above
2. Go to **Network Admin → Plugins**
3. Click **Network Activate** to enable on all sites, or activate individually per site

### Troubleshooting Installation

**Issue: Plugin fails to activate**
- Ensure PHP 8.3+ and WordPress 6.8+ requirements are met
- Check that ACF Pro is installed and activated first

**Issue: No References menu in admin**
- Verify the plugin is activated (not just installed)
- Check for PHP errors in **Tools → Site Health → Info → Server**

**Issue: Templates not displaying correctly**
- Ensure your theme supports Bootstrap 5 markup, or customize the templates
- Enable `WP_DEBUG` to load debug templates for troubleshooting

**Issue: Interactive filters not working**
- Verify that JavaScript is enabled in the browser
- Check browser console for JavaScript errors
- Ensure Bootstrap 5 JavaScript is loaded (required for dropdown functionality)
- Confirm that `show_filters="true"` attribute is set in the shortcode

**Issue: Filter dropdowns appear empty**
- Verify that taxonomy terms are assigned to references
- Check that references are published (not draft)
- Ensure the correct taxonomy slug is used
- Confirm `show_filter` attribute includes the correct filter indexes (0, 1, or 2)


## Changelog

### 1.0.1
* Fix for incorrect database content caused by WPML

### 1.0.0
* Initial Release
