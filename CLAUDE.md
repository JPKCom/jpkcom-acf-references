# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

This is a WordPress plugin called **JPKCom ACF References** - a reference gallery system with filter functions built on Advanced Custom Fields Pro. It provides custom post types (references, locations, customers), custom taxonomies, and a complete template system for displaying reference projects, portfolios, and case studies.

**Requirements:**
- WordPress 7.0+
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
- `shortcodes/partials/list-cards.php`, `shortcodes/partials/list-items.php`, `shortcodes/partials/list-images.php` - layout-specific shortcode partials

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

**`[jpkcom_acf_references_list]`** - Filtered reference list with interactive filter controls:

**Filtering & Query Attributes:**
- `type` - CSV of reference-type term IDs (e.g., "1,5")
- `filter_1` - CSV of reference-filter-1 term IDs
- `filter_2` - CSV of reference-filter-2 term IDs
- `customer` - CSV of customer post IDs
- `location` - CSV of location post IDs
- `limit` - Number of posts (default: -1 for all)
- `sort` - "ASC" or "DSC" (default: "DSC")

**Filter UI Attributes:**
- `show_filters` - Enable interactive filter dropdowns (true/false, default: false)
- `show_filter` - CSV of which filters to show: 0=type, 1=filter_1, 2=filter_2 (e.g., "0,1")
- `reset_button` - Show "Reset all filters" button (true/false, default: false)
- `filter_title_0` - Custom label for reference-type filter (default: "Reference Type")
- `filter_title_1` - Custom label for filter 1 (default: "Filter 1")
- `filter_title_2` - Custom label for filter 2 (default: "Filter 2")

**Display Attributes:**
- `layout` - Display style: "list", "cards", or "images" (default: "list")
- `style` - Inline CSS
- `class` - CSS classes
- `title` - Section headline

**Layout Options:**
- `list` - Uses `shortcodes/partials/list-items.php` (compact list view)
- `cards` - Uses `shortcodes/partials/list-cards.php` (card grid with thumbnails and details)
- `images` - Uses `shortcodes/partials/list-images.php` (full-width image overlay cards with minimal text, no borders or gaps)

**Featured References:**
References with `reference_featured` field set to true receive additional CSS class `jpkcom-acf-ref-featured` for styling.

**Examples:**

Cards layout with interactive filters:
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1" reset_button="true" filter_title_0="Projekttyp" filter_title_1="Kategorie" layout="cards" limit="10" class="mb-5" title="Our References"]
```

Image overlay layout (full-width, borderless):
```
[jpkcom_acf_references_list layout="images" limit="12" class="mb-5"]
```

**`[jpkcom_acf_references_types]`** - Display reference types taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

**`[jpkcom_acf_references_filter_1]`** - Display reference filter 1 taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

**`[jpkcom_acf_references_filter_2]`** - Display reference filter 2 taxonomy as `<details>` elements:
- `id` - CSV of term IDs (optional, shows all if omitted)
- `style`, `class`, `title` - Same as above

### Frontend Assets

The plugin includes CSS and JavaScript assets located in the `assets/` directory and enqueued via `includes/assets-enqueue.php`:

**CSS (`assets/css/reference-styles.css`):**
- **Image hover effects:** Zoom transitions on cards and gallery thumbnails (respects `prefers-reduced-motion`)
- **Filter animations:** Smooth fade-in/fade-out with translateY and scale effects (0.3s duration)
  - Fade-out: Items move up 8px and scale to 98% while fading to transparent
  - Fade-in: Items fade in from transparent and scale back to 100%
  - Uses CSS classes: `is-hiding`, `is-preparing-show`, `is-showing`, `is-hidden`
- **Accessibility styles:** visually-hidden class, ARIA support
- Uses `@media (prefers-reduced-motion: no-preference)` to apply smooth transitions only for users without motion sensitivity
- Users with `prefers-reduced-motion: reduce` see instant show/hide without animations

**JavaScript (`assets/js/reference-list-filter.js`):**
- Client-side filtering for `[jpkcom_acf_references_list]` shortcode with animated transitions
- Vanilla JavaScript (no jQuery dependency)
- Handles dropdown filter interactions
- Updates visible reference items based on selected taxonomy filters
- Manages animation states with proper timing for smooth transitions
- Detects `prefers-reduced-motion` setting and skips animations accordingly

**JavaScript (`assets/js/gallery-modal.js`):**
- Image gallery lightbox modal functionality
- Keyboard navigation (arrow keys, Escape to close)
- Previous/Next button handlers
- Image counter updates ("Image X of Y")
- Focus management for accessibility

**JavaScript (`assets/js/shortcode-generator.js`):**
- Interactive shortcode generator in admin (References → Shortcodes)
- Live shortcode preview with all attributes
- Clipboard API with fallback for copying generated shortcodes
- Form validation and dynamic field visibility

**CSS (`assets/css/admin-styles.css`):**
- Styling for admin pages (shortcode generator, settings)

**Enqueuing:**
Assets are automatically enqueued via `includes/assets-enqueue.php`:
- Frontend CSS/JS: Enqueued on all pages via `wp_enqueue_scripts` (priority 20)
- Admin CSS/JS: Enqueued on admin pages via `admin_enqueue_scripts` (priority 10)
- All files versioned using `JPKCOM_ACFREFERENCES_VERSION` constant for cache busting

**Accessibility Features:**
- Respects user motion preferences via `prefers-reduced-motion` media query
- Animations disabled for users with motion sensitivity
- ARIA labels and live regions for filter interactions
- Keyboard-accessible filter controls and modal navigation
- Screen reader announcements for filter results

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
- **Checksum is mandatory (fail closed):** a manifest without `checksum_sha256`, or one that cannot be fetched, aborts the update instead of installing unverified code. There is deliberately no "skip verification" fallback — that would let anyone who can alter the manifest disable the integrity check by dropping one field.
- The verified temp file is returned from `upgrader_pre_download`, so WordPress installs exactly the bytes that were hashed (previously the package was fetched a second time and *those* bytes were installed)
- Failed manifest fetches are negatively cached for 1 h, so an unreachable host cannot stall admin requests once per plugin
- URL validation and sanitization using `wp_http_validate_url()`
- Race condition prevention with transient locking mechanism
- Comprehensive error logging in `WP_DEBUG` mode

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

### API Documentation

`phpdoc.xml` configures phpDocumentor to document `jpkcom-acf-references.php` and `includes/*.php` into `docs/`. The release workflow generates it and publishes to `https://jpkcom.github.io/jpkcom-acf-references/docs/`. `.phpdoc/`, `docs/` and `phpDocumentor.phar` are gitignored — locally: `./phpDocumentor.phar run --config=phpdoc.xml`.

### Version Management

The version appears in five places and must be kept in sync:

1. `jpkcom-acf-references.php` — header `Version:`
2. `jpkcom-acf-references.php` — header `Stable tag:`
3. `jpkcom-acf-references.php` — `JPKCOM_ACFREFERENCES_VERSION`
4. `phpdoc.xml` — `<version number="…">`
5. `README.md` — `**Version:**`, `**Stable tag:**`, plus a new `### x.y.z` changelog block

### Release Process

**Actions are pinned to commit SHAs.** Every `uses:` line in `.github/workflows/` references a 40-character commit SHA instead of a tag (`@v4`), with the version as a trailing comment. A tag is a movable pointer and can be repointed; a SHA cannot. Since the release workflow builds the plugin ZIP **and** the SHA256 checksum the auto-updater trusts, a compromised action would ship a tampered ZIP together with a matching checksum — the checksum secures the transport, the pinning secures the build. `.github/dependabot.yml` keeps the pins current weekly in one combined PR; when updating, always change the SHA *and* the version comment together.

**CI** (`.github/workflows/ci.yml`) runs on every pull request *and* on every push to `main` — a required status check only covers pull requests, so a direct push with bypass rights would otherwise skip the checks entirely. It runs `php -l` over all PHP files; flags invalid named arguments to internal PHP functions (catches `sprintf(format:, values:)` → `ArgumentCountError`, which `php -l` does not see); validates the YAML of every `.github` file; asserts every action is pinned to a 40-character commit SHA; and executes `tests/test-*.php` where present.

**Dependabot auto-merge** (`.github/workflows/dependabot-auto-merge.yml`) merges only `semver-patch` and `semver-minor`, and only PRs from `dependabot[bot]` in this repo — never from forks. Major updates get a comment and stay manual. Two repo settings are prerequisites, otherwise this is useless or outright dangerous: "Allow auto-merge" must be enabled, and branch protection must list `CI / Lint & Guards` as a **required status check** — without it `gh pr merge --auto` merges *immediately*, since there is nothing left to wait for. Together with `cooldown: default-days: 7` no action release is adopted during its first week.

**Releasing.** Bump the five version locations, add the changelog block, commit, then push a `v*` tag — that tag push is the only trigger. `.github/workflows/release.yml` creates the GitHub release itself; do **not** create it by hand first. Pipeline: README metadata via Pandoc → slug-named ZIP (excludes `.git`, `.github`, `CLAUDE.md`, `tests`, `tools`, `docs`, build artefacts) → SHA256 → upload ZIP + `.sha256` → `plugin_jpkcom-acf-references.json` manifest → PHPDoc → deploy to `gh-pages`.

The manifest's `checksum_sha256` is what `includes/class-plugin-updater.php` verifies on every update, so the ZIP and the manifest must come from the same run — which is why the manifest is only rebuilt on a tag push.

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

**CRITICAL:** The `wpml_cf_preferences` values in ACF field definitions MUST match the actions in `wpml-config.xml`:

- `wpml_cf_preferences => 0` = `action="ignore"` (ACF internal fields only)
- `wpml_cf_preferences => 1` = `action="copy-once"` (copied once, then independent)
- `wpml_cf_preferences => 2` = `action="translate"` (content differs per language)
- `wpml_cf_preferences => 3` = `action="copy"` (kept in sync across translations - RARELY USED)

All field groups MUST include `'acfml_field_group_mode' => 'translation'` for proper WPML integration.

Three action types control how fields are handled across languages:

1. **`action="translate"`** (`wpml_cf_preferences => 2`) - Content differs per language:
   - `reference_short_description`
   - `reference_location_place`
   - `reference_location_street`
   - `reference_location_region`
   - `reference_location_country`

2. **`action="copy-once"`** (`wpml_cf_preferences => 1`) - Copied once, then independent:
   - **IMPORTANT:** This is the default for most fields!
   - `reference_url`
   - `reference_customer`, `reference_location` (with `translate_link_target="1"` for auto Post-ID translation)
   - `reference_year`, `reference_image_gallery`
   - `reference_type`, `reference_filter_1`, `reference_filter_2`
   - `reference_featured`, `reference_expiry_date`
   - All customer fields (`reference_customer_url`, `reference_customer_logo`, `reference_customer_references`)
   - Location zip code (`reference_location_zip`)
   - Bidirectional field `reference_location_references`

3. **`action="copy"`** (`wpml_cf_preferences => 3`) - Kept in sync across translations:
   - **NOT USED** in this plugin (causes issues with arrays and objects)

**ACF Internal Fields (Prefixed with `_`):**

WPML requires special handling of ACF's internal meta fields:

- **Most fields:** `action="ignore"` - These store field keys, not content (e.g., `_reference_type`, `_reference_url`)
- **Post Object fields:** `action="copy-once"` - EXCEPTION for fields that link to other posts
  - `_reference_customer` - Must be copied for ACF to format the Post Object correctly
  - `_reference_location` - Must be copied for ACF to format the Post Object correctly
  - `_reference_customer_references` - Bidirectional field key
  - `_reference_location_references` - Bidirectional field key

Without these internal field keys copied to translations, ACF cannot properly format field values and will return raw database values (IDs instead of post objects).

**Translation Files:**
- Located in `languages/` directory
- Format: `.l10n.php` (WordPress 6.8+ format), `.po` (source), `.mo` (compiled)
- Text domain: `jpkcom-acf-references`
- German translations included: `de_DE` and `de_DE_formal`

**Important Notes:**

**Bidirectional Post Object Fields:**
- Fields like `reference_location` and `reference_customer` use `wpml_cf_preferences => 1` (copy-once) with `translate_link_target="1"` in wpml-config.xml
- The `translate_link_target="1"` attribute is **CRITICAL** - it tells WPML to automatically translate Post IDs to their translated versions
- Without this, the field would show the wrong post (e.g., showing the Reference title instead of Location/Customer title)
- Example in wpml-config.xml:
  ```xml
  <custom-field action="copy-once" translate_link_target="1">reference_customer</custom-field>
  <custom-field action="copy-once" translate_link_target="1">reference_location</custom-field>
  ```

**WPML + ACF Field Keys Automatic Fix:**
- The plugin includes `includes/wpml-acf-field-keys-fix.php` which automatically copies ACF field keys to translations
- This hook runs on `wpml_pro_translation_completed` and `wpml_translation_job_saved`
- Without this fix, ACF fields in translations would show raw values (IDs, serialized strings) instead of formatted objects
- The fix copies all `_field_name` meta keys from the original post to the translation
- This is essential for Post Object fields, Gallery fields, and other complex ACF field types

**Python Scripts:**
- `update-json-from-wpml.py` - Syncs ACF JSON export with wpml-config.xml
- Uses the correct mapping: ignore=0, copy-once=1, translate=2, copy=3
- Automatically adds `acfml_field_group_mode => 'translation'` to all field groups

> **Project decision: the Abilities API stays English, in every language.** All 75 strings from
> `includes/abilities.php` are in the catalogue and all 75 are untranslated, deliberately. They are
> read by MCP clients and agents, not by people in wp-admin, and their wording is the feature — a
> message that names the accepted keys is what lets a caller correct itself in one turn. **Do not
> "complete" them**, and do not read an empty `msgstr` on an `abilities.php` string as a backlog.

> **`tests/test-i18n.php` fails the build when the catalogue falls behind the code**, and also when
> the compiled `.l10n.php` and its `.po` disagree. Since WordPress 6.5 the `.l10n.php` is the format
> core loads FIRST and is not a by-product of the `.po`: `wp i18n make-php` writes it FROM the `.po`
> and therefore deletes anything only the compiled file carries. In the sibling plugin that destroyed
> 27 German translations. Compare the entry counts before running it.

**Updating Translation Files:**
```bash
# Extract strings from all PHP files
xgettext --language=PHP --from-code=UTF-8 --keyword=__ --keyword=_e --keyword=esc_html__ --keyword=esc_html_e --keyword=esc_attr__ --keyword=esc_attr_e --keyword=_x:1,2c --keyword=_ex:1,2c --keyword=esc_attr_x:1,2c --keyword=esc_html_x:1,2c --keyword=_n:1,2 --keyword=_nx:1,2,4c --keyword=_n_noop:1,2 --keyword=_nx_noop:1,2,3c --sort-output --package-name="JPKCom ACF References" --package-version="1.0.0" --msgid-bugs-address="jpk@jpkc.com" -o languages/jpkcom-acf-references.pot *.php includes/*.php templates/**/*.php

# Merge with existing translations
msgmerge --update --backup=none languages/jpkcom-acf-references-de_DE.po languages/jpkcom-acf-references.pot

# Compile to binary .mo files
msgfmt languages/jpkcom-acf-references-de_DE.po -o languages/jpkcom-acf-references-de_DE.mo

# Generate .l10n.php (WordPress 6.8+ performance optimization)
wp i18n make-php languages/
```

## Abilities API (since 1.2.0)

`includes/abilities.php` registers three **read-only** abilities in the `jpkcom-content` category
the sibling plugins share. Categories are global and **first-wins**, so registration goes through
`wp_has_ability_category()` rather than assuming.

| Ability | Returns |
|---|---|
| `jpkcom-acf-references/list-filters` | The three taxonomies with their terms, plus customers and locations, and how many published references the listing rule excludes |
| `jpkcom-acf-references/query-references` | A filtered, paginated list of compact reference records |
| `jpkcom-acf-references/get-reference` | One reference, with a `detail` block when its page actually renders |

Everything in `includes/abilities.php` reads through `includes/references-data.php`, and the rule is
not restated inside the ability callbacks.

> **But it does not yet have one home, and an earlier version of this section claimed it did.**
> `jpkcom_acf_references_build_reference_query_args()` is called from `includes/abilities.php` and
> from nowhere else; `includes/shortcodes.php` still assembles the same `post_type`, `meta_query` and
> `tax_query` clauses inline (`:147` onwards). They agree today — the extraction was verified against
> the running site by comparing the generated SQL and the returned IDs with the shortcode's own
> arguments — but agreeing is not sharing, and the two can drift the moment either side is edited.
> Treat a change to the visibility rule as a change to **both** files until the shortcode is moved
> over. The sibling `jpkcom-acf-jobs` is the finished shape of this: there the shortcode, the archive
> and the abilities all call one builder.

### What will bite

**1. `get_field()` is not a read operation on `reference_short_description`.** ACF pipes wysiwyg
values through `acf_the_content`, which carries `do_shortcode` at priority 11 and
`WP_Embed::autoembed` at 8. In an ability callback there is no post context, so `WP_Embed::shortcode()`
fetches the remote URL and calls `wp_insert_post()` with `post_type => 'oembed_cache'` — a declared
read-only ability writing rows and making outbound requests, triggerable by any subscriber. A bare
URL alone on one line of a description is enough.

Measured on this plugin, not argued: the formatted read wrote a row and executed the shortcode
(`posts 61 → 62`, `oembed_cache 0 → 1`); `get-reference` on the same record as a subscriber changed
nothing. The measurement is only meaningful because the ability demonstrably READ the field — the
bare URL came back as a literal and the `[gallery]` shortcode unexecuted. `tests/test-abilities.php`
fails the build if that read loses its `, false`.

**2. The two visibility counts are DIFFERENCES.** Never a second statement of what "expired" means.
The rule's expiry clause is an OR group of three branches — at or after today, no row, and the
empty string — and negating only the first is not its complement: MariaDB casts `''` to
`'0000-00-00'`, and `''` is the ordinary case because ACF writes it rather than deleting the row.

    hidden_expired          = ( published + featured row ) - listed
    hidden_missing_featured = published - ( published + featured row )

Two independent causes exclude a reference with no `reference_featured` row — the `EXISTS` clause
**and** the `meta_key` used for ordering, whose own condition lands in the WHERE clause — so the
count of references carrying the row cannot be read off the listing query and needs one of its own.

**3. A filter value that resolves to nothing must return nothing.** Skipping the clause answers with
the complete unfiltered corpus behind an HTTP 200 while `unknown` names the reason only in passing,
and a caller reading `total` gets the whole site as a filtered answer. This shipped in the first
draft here and is guarded by the runtime checks; a partly recognisable filter still narrows by the
part that resolved.

**4. `listed` is answered BY the rule, not by a paraphrase of it.** `get-reference` runs the listing
query restricted to that one ID. A PHP re-derivation disagreed with the SQL in the sibling plugin for
a stored date of `'2025-11-30 00:00:00'`, because `CAST(... AS DATE)` parses it and
`DateTimeImmutable` does not.

**5. The unknown-key guard is on ALL THREE abilities.** A guard on a subset is a trap: a caller that
learned the refusal on one assumes it everywhere, and the ability that silently accepts is the one it
will trust. The sibling plugin shipped exactly that gap for a whole release with a green suite,
because every assertion written for the guard targeted an ability that had it. The checks here are
structural for that reason.

**6. `additionalProperties` is deliberately NOT declared** on the two schemas that carry
`properties`. Measured on WP 7.0.3: it is safe, but `validate_input()` runs **before** the execute
callback, so it preempts the guard and the reply degrades from a message naming the accepted keys to
core's "not a valid property of the object". `list-filters` is the exception and must stay one — it
declares no `properties` at all (an empty `stdClass` there is an anonymous fatal, an empty array is
invalid JSON Schema), so `additionalProperties => false` is the only thing that can refuse a key.

**7. Every parameter is optional, and that still needs a top-level `default`.**
`normalize_input()` substitutes the schema's top-level default only when the input is exactly `null`,
and it must be an **object** — the MCP adapter publishes `get_input_schema()` verbatim. The callbacks
therefore accept a `stdClass` and read it.

**8. The search limit counts BYTES.** `WP_Query::parse_query()` empties `s` over 1600 bytes using
`strlen()`, inside the query and past every check on the arguments. A `maxLength` in the schema counts
characters and would leave the hole open for exactly the input most likely to hit it.

### Exposure

Three switches in `meta`: `show_in_rest`, `public` (WP 7.1; inert before), and `mcp.public` — the MCP
adapter's own gate for discovery *and* execution. `JPKCOM_ACFREFERENCES_ABILITIES = false` in
`wp-config.php` suppresses registration entirely; per-ability control goes through
`jpkcom_acf_references_ability_meta` and `jpkcom_acf_references_ability_capability` (default `read`).

Listing abilities over REST is gated only by `current_user_can( 'read' )`, so **every logged-in user
can read all three abilities' labels, descriptions and full schemas.** Execution is gated by the
permission callback, and every query is hard-scoped to `post_status => 'publish'`.

**The Abilities API messages stay English, in every language** — see the note in the translation
section. They are read by MCP clients and agents, and their wording is the feature.

### Verifying against a real installation

`wp ability` needs WP-CLI >= 2.13 and DDEV ships 2.12, so use `ddev wp eval-file <file>.php` with the
file inside the DDEV project root. Over HTTP, with an application password:

```bash
BASE=https://your-site.test/wp-json/wp-abilities/v1/abilities
curl -skg -u "$USER:$APP_PW" "$BASE/jpkcom-acf-references/list-filters/run"
curl -skg -u "$USER:$APP_PW" "$BASE/jpkcom-acf-references/query-references/run"
curl -skg -u "$USER:$APP_PW" "$BASE/jpkcom-acf-references/get-reference/run?input[id]=165"
```

The checks that matter, and none of them is a code read: filtering must **narrow** (compare the
filtered `total` against the unfiltered one); the three visibility numbers must add up to
`published` exactly; a caller mistake must be 4xx and never 5xx; `POST` on a run route must be 405;
and the read-only claim must be measured with a **mutation control** — count `oembed_cache` rows
around an unguarded `get_field()` read first, to prove the probe can see a write at all, before
concluding that the ability does not write.

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

- `JPKCOM_ACFREFERENCES_VERSION` - Plugin version (currently `1.1.0`)
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
- **Shortcodes** (admin page) - Interactive shortcode generator
- **Options** (admin page) - Archive redirect settings

### Admin Pages

**Shortcode Generator** (`includes/admin-pages.php` - References → Shortcodes):
- Visual form for generating `[jpkcom_acf_references_list]` shortcodes
- All attributes available with descriptions and examples
- Live shortcode preview
- One-click copy to clipboard
- Pre-fill form fields for type, filter_1, filter_2, customer, location (comma-separated IDs)
- Shows other available shortcodes with documentation

**Options Page** (`includes/admin-pages.php` - References → Options):
- **Disable Reference Archive**: Redirect visitors from `/references/` archive page
- **Archive Redirect URL**: Custom redirect URL (defaults to homepage)
- Uses HTTP 307 (Temporary Redirect) status
- Single reference pages remain accessible even when archive is disabled
- Redirect logic in `includes/redirects.php`

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
- `[jpkcom_acf_references_list]` - Filterable reference grid with interactive filters
- `[jpkcom_acf_references_types]` - Interactive type filter
- `[jpkcom_acf_references_filter_1]` - Custom filter 1 display
- `[jpkcom_acf_references_filter_2]` - Custom filter 2 display

### Interactive Frontend Filtering

The `jpkcom_acf_references_list` shortcode supports JavaScript-based client-side filtering:

**How it works:**
1. All references are loaded on page load
2. Filter dropdowns are populated with available taxonomy terms
3. Users can select multiple filter values from dropdowns
4. Filtering happens instantly without page reload
5. "No references found" message displays when no matches
6. "Reset all filters" button clears all active filters

**JavaScript implementation:**
- Vanilla JavaScript (no jQuery dependency)
- Uses Bootstrap 5 dropdown components
- Each reference item has `data-*` attributes for filtering (e.g., `data-type="1,5"`)
- Filters use "OR" logic within a taxonomy, "AND" logic between taxonomies
- Featured references maintain their CSS class for visual priority

**Example with filters enabled:**
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1,2" reset_button="true" layout="cards"]
```

**Custom filter labels:**
```
[jpkcom_acf_references_list show_filters="true" show_filter="0,1" filter_title_0="Project Type" filter_title_1="Industry" reset_button="true"]
```

## Custom Image Sizes

The plugin registers custom WordPress image sizes in `includes/media.php` for optimized display across different layouts:

**Available Image Sizes:**
- `jpkcom-acf-reference-16x9` - 576x324px (16:9 aspect ratio, hard crop)
  - Used by: Cards layout (`shortcodes/partials/list-cards.php`)
  - Purpose: Thumbnail images for card-based reference listings

- `jpkcom-acf-reference-card-overlay` - 800x600px (4:3 aspect ratio, hard crop)
  - Used by: Images layout (`shortcodes/partials/list-images.php`)
  - Purpose: Large overlay images with uniform height for consistent card display
  - Hard crop ensures all cards have identical dimensions

- `jpkcom-acf-reference-header` - 992x558px (16:9 aspect ratio, hard crop)
  - Used by: Archive pages and single reference headers
  - Purpose: Large header/hero images

- `jpkcom-acf-reference-logo` - 512x512px (square, hard crop)
  - Used by: Customer and location logos
  - Purpose: Square logo images

**Image Generation:**
All sizes use hard crop (`true` parameter) to maintain exact dimensions. After adding new image sizes or updating existing ones, regenerate thumbnails for existing media using a plugin like "Regenerate Thumbnails" or WP-CLI:

```bash
wp media regenerate --yes
```

**Template Usage:**
```php
// Get specific image size
$thumbnail = get_the_post_thumbnail( $post_id, 'jpkcom-acf-reference-card-overlay' );

// With custom CSS classes
$thumbnail = get_the_post_thumbnail( $post_id, 'jpkcom-acf-reference-16x9', [ 'class' => 'card-img rounded-0' ] );
```

## Debugging

`WP_DEBUG` does double duty here: besides the usual error logging it switches template loading to `debug-templates/` and turns on the plugin updater's detailed logging.

## Security & Correctness

Beyond the usual escaping and sanitising, the first two rules below have a guard behind them in `tests/test-conventions.php`, which CI runs on every pull request and push to `main`:

- **Dates:** use `current_time( 'Y-m-d' )`, never `date( 'Y-m-d' )`. WordPress sets the PHP timezone to UTC in `wp-settings.php`, so `date()` returns the UTC date — expiry comparisons then lag the site timezone by its offset and keep expired references visible for 1–2 hours after local midnight.
- **Capabilities:** never pass a role name to `current_user_can()`. It works only because the role is a key in the capability array, which bypasses `map_meta_cap` and misses differently named roles holding the same rights. Check a capability (`manage_options`, `edit_post`).
- **By design, worth knowing** (not guarded, and not a bug): anyone who can edit a reference can point `reference_url` at any external target, and `redirects.php` 307s the reference URL there using `wp_redirect()` — deliberately not `wp_safe_redirect()`. That is the plugin's purpose, but it does mean a non-admin editor can use the site's own domain as a redirector.

---

## Filtering: tax_query (was: meta LIKE)

The three taxonomy filters of the list shortcode run through `tax_query`
(`includes/shortcodes.php`). They previously used `meta_query` with
`LIKE '%"5"%'` over the serialised ACF values; a leading wildcard cannot use an
index, so each clause scanned every meta row for that key — a fully populated
filter produced six such scans. All three ACF fields are configured with
`save_terms => 1`, so ACF also writes real term relationships, and those are
indexed.

Three details are load-bearing and are pinned by `tests/test-tax-query.php`:

- **`include_children => false`.** These taxonomies are hierarchical, and
  `tax_query` defaults this to `true`. The meta LIKE variant matched only the
  exact term IDs stored in the field, so the default would silently widen
  results to posts filed under child terms.
- **`absint()` instead of `sanitize_text_field()`.** `field => 'term_id'` needs
  integers, and dropping the resulting zeros means non-numeric junk produces no
  clause rather than one that matches nothing.
- **`reference_customer` and `reference_location` stay meta-based.** They are ACF
  post-object fields, not taxonomies — there is no term relationship to query.

**Before deploying this against existing data,** meta and term assignments must
agree for every existing post. They can drift: an import, a direct DB write, or
a WPML duplication that never ran ACF's save routine leaves the meta set and the
relationship missing. Run the read-only checker:

```bash
wp eval-file wp-content/plugins/jpkcom-acf-references/tools/check-term-sync.php
```

Exit code 0 means the two stores agree everywhere; exit code 1 lists the
diverging posts with edit links (re-saving a post makes ACF rewrite both
stores). Re-save those posts before the release goes out, otherwise the switch
quietly changes which references are listed.

Two things about this script were broken until 2026-07-28, and both made it
useless as a gate:

- **It could not run at all.** A `declare(strict_types=1)` sat halfway down the
  file. `wp eval-file` evaluates the file contents, and there the declaration
  must be the very first statement — so the documented invocation ended in a
  fatal error, every time.
- **It reported every translated post as drifted.** It compared raw meta against
  `wp_get_object_terms()`, which WPML rewrites to the *current* language. Running
  in the default language, a Hungarian post with a correct Hungarian relation
  came back carrying the German term while its meta held the Hungarian ID.
  `jpkcom_acfref_object_term_ids()` now reads each post's terms in that post's own
  language; on a monolingual site it is a no-op. Verified both ways on a
  multilingual production copy: 62 posts clean, and an injected mismatch on a
  translated post still reported, with the correct Hungarian IDs.

Also worth knowing: on an empty site exit 0 is trivially true. Run it against
the data you intend to ship against.

### Verified on a live installation (2026-07-28)

DDEV instance, 12 references seeded through `update_field()` so ACF writes both
stores, 3 reference-types × 2 × 2 filter terms. Both query variants were built
in the same process against the same data and their result sets compared over 14
filter combinations (single term, several terms in one group, across groups, and
unfiltered):

| Data | `tools/check-term-sync.php` | Diverging filter cases |
|---|---|---|
| clean | exit 0 | **0 of 14** |
| drift injected into 3 of 12 posts | exit 1, naming exactly those 3 | **6 of 14** |

That is the whole argument for the gate: on clean data the switch is invisible,
on drifted data it silently changes which references are listed, and the checker
identifies precisely the posts responsible. Drift was produced three ways — meta
kept while the term relation was removed, meta overwritten via
`update_post_meta()` while the relation stayed, and meta deleted with the
relation intact.

**Serialisation matters, and this is a point in favour of the switch.** The LIKE
clause searches for `"10"` — with the quotes — which only appears when the term
ID was serialised as a *string* (`s:2:"10"`). The admin form always submits
strings, so that is the normal case. But `update_field( 'reference_type', [ 10 ]
)` with integers stores `i:10`, and then **every LIKE clause silently matches
nothing** while the list renders as if no reference qualified. Observed here: an
integer-seeded data set returned 0 results for all 13 filtered cases and 12 for
the unfiltered one. `tax_query` is immune — it never looks at the meta value.

### WPML: measured — the switch *fixes* the secondary language

Measured on a production copy (WPML 4.9.5, de/hu/pl, 62 references of which 6
translated into Hungarian). Both query variants were rendered by one shortcode
in the same frontend request, so the language context is identical for both.
The filter asks for the **German** term ID 22 — which is what a shortcode with a
hardcoded `type="22"` does on every page, in every language:

| Language | `meta_query` + `LIKE` (old) | `tax_query` (new) |
|---|---|---|
| de | 34 references | 34 references, identical set |
| hu | **0 references** | **6 references** (the Hungarian ones) |

`WPML_Query_Parser::adjust_taxonomy_query()` rewrites the `terms` of every
`tax_query` condition to the current language
(`classes/query-filtering/class-wpml-query-parser.php`). There is no counterpart
for `meta_query` and there cannot be — WPML has no way to know a serialised meta
value holds a term ID. So the old filter looked for `"22"` in posts whose meta
holds `"43"` and found nothing.

**On a multilingual site the old behaviour is a silently empty list in every
secondary language.** This migration is not a risk there, it is the fix. Verify
the secondary language after deploying — but expect it to gain content, not lose
it.
- Database queries use WordPress prepared statements
- Plugin updater verifies SHA256 checksums before installation
