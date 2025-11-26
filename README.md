# JPKCom ACF References

**Plugin Name:** JPKCom ACF References  
**Plugin URI:** https://github.com/JPKCom/jpkcom-acf-references  
**Description:** Reference gallery with filter function plugin for ACF  
**Version:** 1.0.0  
**Author:** Jean Pierre Kolb <jpk@jpkc.com>  
**Author URI:** https://www.jpkc.com/  
**Contributors:** JPKCom  
**Tags:** ACF, Fields, CPT, CTT, Taxonomy, Images  
**Requires Plugins:** advanced-custom-fields-pro, acf-quickedit-fields  
**Requires at least:** 6.8  
**Tested up to:** 6.9  
**Requires PHP:** 8.3  
**Network:** true  
**Stable tag:** 1.0.0  
**License:** GPL-2.0+  
**License URI:** http://www.gnu.org/licenses/gpl-2.0.txt  
**Text Domain:** jpkcom-acf-references  
**Domain Path:** /languages

A plugin to provide a reference gallery with filter function tool for ACF Pro.


## Description

**JPKCom ACF References** is a reference gallery system with filter functions built on Advanced Custom Fields Pro. This plugin provides a complete solution for creating, managing, and displaying reference projects on your WordPress website with powerful features for portfolios, case studies, and project showcases.

### Key Features

- **Three Custom Post Types**: References, Locations, and Customers with hierarchical organization
- **Image Galleries**: Full support for image galleries with flexible display options
- **Advanced Filtering**: Filter references by type, location, customer, and custom attributes
- **Multilingual Ready**: Full WPML support with translation-aware field configuration
- **Template Override System**: Customize any template via child theme, parent theme, or mu-plugins
- **Developer-Friendly**: Helper functions, filters, and shortcodes for easy customization
- **Bootstrap 5 Ready**: Pre-styled templates with modern responsive markup
- **Automatic Updates**: Secure GitHub-based plugin updates with SHA256 checksum verification

### Requirements

The following plugins are **required** for this plugin to work:

- [Advanced Custom Fields Pro](https://www.advancedcustomfields.com/) (v6.0+)
- [ACF Quick Edit Fields](https://wordpress.org/plugins/acf-quickedit-fields/) (for inline editing)

**Optional:**
- [WPML](https://wpml.org/) for multilingual reference projects

### What's Included

- **Custom Fields** (`includes/acf-field_groups.php`) - Programmatically registered ACF field groups for references, locations, and customers
- **Custom Post Types** (`includes/acf-post_types.php`) - Reference, Location, and Customer post types with proper admin organization
- **Custom Taxonomies** (`includes/acf-taxonomies.php`) - Reference types taxonomy for categorization
- **Template System** (`templates/`) - Complete set of single and archive templates with override support
- **Shortcodes** (`includes/shortcodes.php`) - Display filtered reference lists and type taxonomies anywhere
- **Helper Functions** (`includes/helpers.php`) - Utility functions for rendering fields and formatting dates

### Get Template Parts

```php
// Native WordPress:
get_template_part( 'jpkcom-acf-references/partials/reference/customer' );
// Plugin:
jpkcom_acf_references_get_template_part( 'partials/reference/customer' );
```

### Shortcodes

All shortcode attributes are optional.

#### Reference list with filter functions:
```
[jpkcom_acf_references_list type="1,5" customer="6,8" location="1,3,7,11" limit="10" sort="DSC" style="background:transparent;" class="mb-5" title="References Headline"]
```

#### List of reference types displayed as `<details>` tags with filter functions:
```
[jpkcom_acf_references_types id="3,7,21" style="background:transparent;" class="mb-5" title="Types Headline"]
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

**Option 1: Use the shortcode**
```
[jpkcom_acf_references_list limit="10" sort="DSC"]
```

**Option 2: Navigate to the archive**
Visit `/references/` on your site to see all published references.

**Option 3: Create a custom template**
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

### What are reference types?

Reference types are custom taxonomy terms (like tags) that you can assign to references. Use them for:
- Project categories: "Web Development", "Design", "Consulting"
- Industries: "Healthcare", "Finance", "Education"
- Technologies: "WordPress", "React", "PHP"

Display them with the shortcode:
```
[jpkcom_acf_references_types]
```

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
3. **Review Settings**: Visit **References → Settings** to configure default options (if available)
4. **Create Test Content**:
   - Create a location: **Locations → Add New**
   - Create a customer: **Customers → Add New**
   - Create a reference: **References → Add New** (assign the location and customer)
5. **View Frontend**: Visit `/references/` on your site to see the reference archive
6. **Add to Navigation** (Optional): Add the reference archive to your site menu via **Appearance → Menus**

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


## Changelog

### 1.0.0
* Initial Release
