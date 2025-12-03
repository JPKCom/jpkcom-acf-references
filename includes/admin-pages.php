<?php
/**
 * Admin Pages and Settings
 *
 * Registers admin pages under the References post type menu and handles settings.
 *
 * @package   JPKCom_ACF_References
 * @since     1.0.0
 */

declare(strict_types=1);

if ( ! defined( constant_name: 'ABSPATH' ) ) {
    exit;
}

/**
 * Register admin menu pages
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_menu', function(): void {

    // Shortcodes page
    add_submenu_page(
        'edit.php?post_type=reference',
        __( 'Shortcodes', 'jpkcom-acf-references' ),
        __( 'Shortcodes', 'jpkcom-acf-references' ),
        'manage_options',
        'jpkcom-acf-ref-shortcodes',
        'jpkcom_acf_references_shortcodes_page'
    );

    // Settings page
    add_submenu_page(
        'edit.php?post_type=reference',
        __( 'Options', 'jpkcom-acf-references' ),
        __( 'Options', 'jpkcom-acf-references' ),
        'manage_options',
        'jpkcom-acf-ref-options',
        'jpkcom_acf_references_options_page'
    );

}, 20 );

/**
 * Register settings
 *
 * @since 1.0.0
 * @return void
 */
add_action( 'admin_init', function(): void {

    // Register settings group
    register_setting(
        'jpkcom_acf_ref_options',
        'jpkcom_acf_ref_disable_archive',
        [
            'type' => 'boolean',
            'default' => false,
            'sanitize_callback' => 'rest_sanitize_boolean',
        ]
    );

    register_setting(
        'jpkcom_acf_ref_options',
        'jpkcom_acf_ref_archive_redirect_url',
        [
            'type' => 'string',
            'default' => '',
            'sanitize_callback' => 'esc_url_raw',
        ]
    );

    // Add settings section
    add_settings_section(
        'jpkcom_acf_ref_archive_section',
        __( 'Archive Settings', 'jpkcom-acf-references' ),
        function() {
            echo '<p>' . esc_html__( 'Configure the reference archive page behavior.', 'jpkcom-acf-references' ) . '</p>';
        },
        'jpkcom-acf-ref-options'
    );

    // Disable archive field
    add_settings_field(
        'jpkcom_acf_ref_disable_archive',
        __( 'Disable Reference Archive', 'jpkcom-acf-references' ),
        'jpkcom_acf_references_disable_archive_field',
        'jpkcom-acf-ref-options',
        'jpkcom_acf_ref_archive_section'
    );

    // Archive redirect URL field
    add_settings_field(
        'jpkcom_acf_ref_archive_redirect_url',
        __( 'Archive Redirect URL', 'jpkcom-acf-references' ),
        'jpkcom_acf_references_redirect_url_field',
        'jpkcom-acf-ref-options',
        'jpkcom_acf_ref_archive_section'
    );

} );

/**
 * Render disable archive checkbox field
 *
 * @since 1.0.0
 * @return void
 */
function jpkcom_acf_references_disable_archive_field(): void {
    $value = get_option( 'jpkcom_acf_ref_disable_archive', false );
    ?>
    <label for="jpkcom_acf_ref_disable_archive">
        <input
            type="checkbox"
            id="jpkcom_acf_ref_disable_archive"
            name="jpkcom_acf_ref_disable_archive"
            value="1"
            <?php checked( $value, true ); ?>
        >
        <?php echo esc_html__( 'Redirect all access to the reference archive page (/references/)', 'jpkcom-acf-references' ); ?>
    </label>
    <p class="description">
        <?php echo esc_html__( 'When enabled, visitors will be redirected from the archive page. Single reference pages remain accessible.', 'jpkcom-acf-references' ); ?>
    </p>
    <?php
}

/**
 * Render archive redirect URL field
 *
 * @since 1.0.0
 * @return void
 */
function jpkcom_acf_references_redirect_url_field(): void {
    $value = get_option( 'jpkcom_acf_ref_archive_redirect_url', '' );
    ?>
    <input
        type="url"
        id="jpkcom_acf_ref_archive_redirect_url"
        name="jpkcom_acf_ref_archive_redirect_url"
        value="<?php echo esc_attr( $value ); ?>"
        class="regular-text"
        placeholder="<?php echo esc_attr( home_url( '/' ) ); ?>"
    >
    <p class="description">
        <?php echo esc_html__( 'Optional: Specify a custom redirect URL. If empty, redirects to the homepage. Only applies when archive is disabled.', 'jpkcom-acf-references' ); ?>
    </p>
    <?php
}

/**
 * Render Shortcodes admin page
 *
 * @since 1.0.0
 * @return void
 */
function jpkcom_acf_references_shortcodes_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Get taxonomies and terms for dropdowns
    $reference_types = get_terms( [ 'taxonomy' => 'reference-type', 'hide_empty' => false ] );
    $filter_1_terms = get_terms( [ 'taxonomy' => 'reference-filter-1', 'hide_empty' => false ] );
    $filter_2_terms = get_terms( [ 'taxonomy' => 'reference-filter-2', 'hide_empty' => false ] );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <div class="jpkcom-acf-ref-shortcode-generator">
            <h2><?php echo esc_html__( 'Reference List Shortcode Generator', 'jpkcom-acf-references' ); ?></h2>
            <p class="description">
                <?php echo esc_html__( 'Configure the options below to generate your shortcode.', 'jpkcom-acf-references' ); ?>
            </p>

            <table class="form-table" role="presentation">
                <tbody>
                    <!-- Layout -->
                    <tr>
                        <th scope="row">
                            <label for="sg_layout"><?php echo esc_html__( 'Layout', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <select id="sg_layout" name="layout">
                                <option value="list"><?php echo esc_html__( 'List (Compact)', 'jpkcom-acf-references' ); ?></option>
                                <option value="cards"><?php echo esc_html__( 'Cards (with Thumbnails)', 'jpkcom-acf-references' ); ?></option>
                                <option value="images"><?php echo esc_html__( 'Images (Overlay)', 'jpkcom-acf-references' ); ?></option>
                            </select>
                            <p class="description"><?php echo esc_html__( 'Choose the display layout for references.', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Limit -->
                    <tr>
                        <th scope="row">
                            <label for="sg_limit"><?php echo esc_html__( 'Number of References', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="number" id="sg_limit" name="limit" value="0" min="0" class="small-text">
                            <p class="description"><?php echo esc_html__( 'Select the number of references to display. 0 = all', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Sort Order -->
                    <tr>
                        <th scope="row">
                            <label for="sg_sort"><?php echo esc_html__( 'Sort Order', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <select id="sg_sort" name="sort">
                                <option value="DSC"><?php echo esc_html__( 'Descending (Newest First)', 'jpkcom-acf-references' ); ?></option>
                                <option value="ASC"><?php echo esc_html__( 'Ascending (Oldest First)', 'jpkcom-acf-references' ); ?></option>
                            </select>
                        </td>
                    </tr>

                    <!-- Title -->
                    <tr>
                        <th scope="row">
                            <label for="sg_title"><?php echo esc_html__( 'Section Title', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_title" name="title" value="" class="regular-text">
                            <p class="description"><?php echo esc_html__( 'Optional headline above the reference list.', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- CSS Class -->
                    <tr>
                        <th scope="row">
                            <label for="sg_class"><?php echo esc_html__( 'CSS Class', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_class" name="class" value="" class="regular-text">
                            <p class="description"><?php echo esc_html__( 'Optional CSS classes for styling (e.g., "mb-5").', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Show Filters -->
                    <tr>
                        <th scope="row">
                            <?php echo esc_html__( 'Enable Filters', 'jpkcom-acf-references' ); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="sg_show_filters" name="show_filters" value="true">
                                <?php echo esc_html__( 'Show filter dropdowns', 'jpkcom-acf-references' ); ?>
                            </label>
                            <p class="description"><?php echo esc_html__( 'Allow visitors to filter references interactively.', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Which Filters -->
                    <tr id="sg_filter_options" style="display: none;">
                        <th scope="row">
                            <?php echo esc_html__( 'Active Filters', 'jpkcom-acf-references' ); ?>
                        </th>
                        <td>
                            <label><input type="checkbox" name="show_filter[]" value="0" checked> <?php echo esc_html__( 'Reference Type', 'jpkcom-acf-references' ); ?></label><br>
                            <label><input type="checkbox" name="show_filter[]" value="1"> <?php echo esc_html__( 'Filter 1', 'jpkcom-acf-references' ); ?></label><br>
                            <label><input type="checkbox" name="show_filter[]" value="2"> <?php echo esc_html__( 'Filter 2', 'jpkcom-acf-references' ); ?></label>
                            <p class="description"><?php echo esc_html__( 'Select which filter dimensions to display.', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Filter Titles -->
                    <tr id="sg_filter_titles" style="display: none;">
                        <th scope="row">
                            <?php echo esc_html__( 'Filter Labels', 'jpkcom-acf-references' ); ?>
                        </th>
                        <td>
                            <input type="text" id="sg_filter_title_0" name="filter_title_0" placeholder="<?php echo esc_attr__( 'Reference Type', 'jpkcom-acf-references' ); ?>" class="regular-text"><br>
                            <label class="description"><?php echo esc_html__( 'Label for Reference Type filter', 'jpkcom-acf-references' ); ?></label><br><br>

                            <input type="text" id="sg_filter_title_1" name="filter_title_1" placeholder="<?php echo esc_attr__( 'Filter 1', 'jpkcom-acf-references' ); ?>" class="regular-text"><br>
                            <label class="description"><?php echo esc_html__( 'Label for Filter 1', 'jpkcom-acf-references' ); ?></label><br><br>

                            <input type="text" id="sg_filter_title_2" name="filter_title_2" placeholder="<?php echo esc_attr__( 'Filter 2', 'jpkcom-acf-references' ); ?>" class="regular-text"><br>
                            <label class="description"><?php echo esc_html__( 'Label for Filter 2', 'jpkcom-acf-references' ); ?></label>
                        </td>
                    </tr>

                    <!-- Reset Button -->
                    <tr id="sg_reset_button_row" style="display: none;">
                        <th scope="row">
                            <?php echo esc_html__( 'Reset Button', 'jpkcom-acf-references' ); ?>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="sg_reset_button" name="reset_button" value="true">
                                <?php echo esc_html__( 'Show "Reset all filters" button', 'jpkcom-acf-references' ); ?>
                            </label>
                        </td>
                    </tr>

                    <!-- Pre-Filter: Reference Type -->
                    <tr>
                        <th scope="row">
                            <label for="sg_type"><?php echo esc_html__( 'Filter by Reference Type', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_type" name="type" value="" class="regular-text" placeholder="1,5,12">
                            <p class="description"><?php echo esc_html__( 'Comma-separated list of reference type term IDs (optional). Example: 1,5,12', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Pre-Filter: Filter 1 -->
                    <tr>
                        <th scope="row">
                            <label for="sg_filter_1"><?php echo esc_html__( 'Filter by Filter 1', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_filter_1" name="filter_1" value="" class="regular-text" placeholder="2,8,15">
                            <p class="description"><?php echo esc_html__( 'Comma-separated list of Filter 1 term IDs (optional). Example: 2,8,15', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Pre-Filter: Filter 2 -->
                    <tr>
                        <th scope="row">
                            <label for="sg_filter_2"><?php echo esc_html__( 'Filter by Filter 2', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_filter_2" name="filter_2" value="" class="regular-text" placeholder="3,7,10">
                            <p class="description"><?php echo esc_html__( 'Comma-separated list of Filter 2 term IDs (optional). Example: 3,7,10', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Pre-Filter: Customer -->
                    <tr>
                        <th scope="row">
                            <label for="sg_customer"><?php echo esc_html__( 'Filter by Customer', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_customer" name="customer" value="" class="regular-text" placeholder="42,87,123">
                            <p class="description"><?php echo esc_html__( 'Comma-separated list of customer post IDs (optional). Example: 42,87,123', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Pre-Filter: Location -->
                    <tr>
                        <th scope="row">
                            <label for="sg_location"><?php echo esc_html__( 'Filter by Location', 'jpkcom-acf-references' ); ?></label>
                        </th>
                        <td>
                            <input type="text" id="sg_location" name="location" value="" class="regular-text" placeholder="5,18,33">
                            <p class="description"><?php echo esc_html__( 'Comma-separated list of location post IDs (optional). Example: 5,18,33', 'jpkcom-acf-references' ); ?></p>
                        </td>
                    </tr>

                    <!-- Generate Button -->
                    <tr>
                        <th scope="row"></th>
                        <td>
                            <button type="button" id="sg_generate" class="button button-primary button-large">
                                <?php echo esc_html__( 'Generate Shortcode', 'jpkcom-acf-references' ); ?>
                            </button>
                        </td>
                    </tr>

                    <!-- Output -->
                    <tr id="sg_output_row" style="display: none;">
                        <th scope="row">
                            <?php echo esc_html__( 'Generated Shortcode', 'jpkcom-acf-references' ); ?>
                        </th>
                        <td>
                            <textarea id="sg_output" readonly class="large-text code" rows="4"></textarea>
                            <p>
                                <button type="button" id="sg_copy" class="button">
                                    <?php echo esc_html__( 'Copy to Clipboard', 'jpkcom-acf-references' ); ?>
                                </button>
                                <span id="sg_copy_feedback" style="display: none; color: green; margin-left: 10px;">
                                    <?php echo esc_html__( 'Copied!', 'jpkcom-acf-references' ); ?>
                                </span>
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <hr>

        <div class="jpkcom-acf-ref-other-shortcodes">
            <h2><?php echo esc_html__( 'Other Available Shortcodes', 'jpkcom-acf-references' ); ?></h2>

            <h3><code>[jpkcom_acf_references_types]</code></h3>
            <p><?php echo esc_html__( 'Displays reference types as expandable details elements.', 'jpkcom-acf-references' ); ?></p>
            <p><strong><?php echo esc_html__( 'Attributes:', 'jpkcom-acf-references' ); ?></strong></p>
            <ul>
                <li><code>id</code> - <?php echo esc_html__( 'Comma-separated list of term IDs (optional, shows all if omitted)', 'jpkcom-acf-references' ); ?></li>
                <li><code>title</code> - <?php echo esc_html__( 'Section headline', 'jpkcom-acf-references' ); ?></li>
                <li><code>class</code> - <?php echo esc_html__( 'CSS classes', 'jpkcom-acf-references' ); ?></li>
                <li><code>style</code> - <?php echo esc_html__( 'Inline CSS', 'jpkcom-acf-references' ); ?></li>
            </ul>
            <p><strong><?php echo esc_html__( 'Example:', 'jpkcom-acf-references' ); ?></strong> <code>[jpkcom_acf_references_types title="Unsere Projekttypen"]</code></p>

            <hr>

            <h3><code>[jpkcom_acf_references_filter_1]</code></h3>
            <p><?php echo esc_html__( 'Displays Filter 1 taxonomy terms as expandable details elements.', 'jpkcom-acf-references' ); ?></p>
            <p><strong><?php echo esc_html__( 'Attributes:', 'jpkcom-acf-references' ); ?></strong> <?php echo esc_html__( 'Same as above', 'jpkcom-acf-references' ); ?></p>
            <p><strong><?php echo esc_html__( 'Example:', 'jpkcom-acf-references' ); ?></strong> <code>[jpkcom_acf_references_filter_1 class="mb-4"]</code></p>

            <hr>

            <h3><code>[jpkcom_acf_references_filter_2]</code></h3>
            <p><?php echo esc_html__( 'Displays Filter 2 taxonomy terms as expandable details elements.', 'jpkcom-acf-references' ); ?></p>
            <p><strong><?php echo esc_html__( 'Attributes:', 'jpkcom-acf-references' ); ?></strong> <?php echo esc_html__( 'Same as above', 'jpkcom-acf-references' ); ?></p>
            <p><strong><?php echo esc_html__( 'Example:', 'jpkcom-acf-references' ); ?></strong> <code>[jpkcom_acf_references_filter_2 id="5,12,18"]</code></p>
        </div>
    </div>
    <?php
}

/**
 * Render Options admin page
 *
 * @since 1.0.0
 * @return void
 */
function jpkcom_acf_references_options_page(): void {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Check if settings were saved
    if ( isset( $_GET['settings-updated'] ) ) {
        add_settings_error(
            'jpkcom_acf_ref_messages',
            'jpkcom_acf_ref_message',
            __( 'Settings saved successfully.', 'jpkcom-acf-references' ),
            'success'
        );
    }

    settings_errors( 'jpkcom_acf_ref_messages' );
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

        <form method="post" action="options.php">
            <?php
            settings_fields( 'jpkcom_acf_ref_options' );
            do_settings_sections( 'jpkcom-acf-ref-options' );
            submit_button();
            ?>
        </form>
    </div>
    <?php
}
