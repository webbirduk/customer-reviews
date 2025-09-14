<?php
/*
 * Plugin Name:          Customer Reviews
 * Plugin URI:           http://wordpress.org/plugins/customer-reviews/
 * Description:          The Customer Review plugin allows you to manage and display customer-submitted reviews for products and services. A short code can be added to any page, post, or custom post type.
 * Version:              1.0.0
 * Author:               Artios Media
 * Author URI:           http://www.artiosmedia.com
 * Developer:            Arafat Rahman
 * Copyright:            © 2019-2025 Artios Media (email : contact@artiosmedia.com).
 * License: GNU          General Public License v3.0
 * License URI:          http://www.gnu.org/licenses/gpl-3.0.html
 * Tested up to:         6.8.1
 * PHP tested up to:     8.2.4
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Create the reviews table on plugin activation
function wp_customer_reviews_table_create() {
      global $wpdb;

    $table_name = $wpdb->prefix . 'customer_reviews';
    // Check if the table already exists
    if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
        return; // Table already exists, no need to create it
    }


    $charset_collate = $wpdb->get_charset_collate();

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    $sql = "CREATE TABLE $table_name (
        id INT(11) NOT NULL AUTO_INCREMENT,
        title VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
        name VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
        email VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
        phone VARCHAR(18) COLLATE utf8mb4_general_ci DEFAULT NULL,
        website VARCHAR(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
        rating INT(1) NOT NULL,
        comment TEXT COLLATE utf8mb4_general_ci NOT NULL,
        city VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
        state VARCHAR(255) COLLATE utf8mb4_general_ci NOT NULL,
        status VARCHAR(20) COLLATE utf8mb4_general_ci DEFAULT 'pending',
        positionid INT(11) DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        admin_reply TEXT COLLATE utf8mb4_general_ci DEFAULT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
  }
  register_activation_hook(__FILE__, 'wp_customer_reviews_table_create');
  

// Define plugin path
define('CTRW_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CTRW_PLUGIN_ASSETS', plugin_dir_url(__FILE__) . 'assets/');
define('CTRW_BASE_NAME', plugin_basename(__FILE__));

// Include MVC structure
include_once CTRW_PLUGIN_PATH . 'includes/ctrw-view.php';
include_once CTRW_PLUGIN_PATH . 'includes/ctrw-model.php';
include_once CTRW_PLUGIN_PATH . 'includes/ctrw-controller.php';

add_action('load-toplevel_page_customer-reviews', 'ctrw_add_screen_options');

function ctrw_add_screen_options() {
    // Add per-page setting
    $option = 'per_page';
    $args = [
        'label'   => 'Reviews per page',
        'default' => 10,
        'option'  => 'ctrw_reviews_per_page'
    ];
    add_screen_option($option, $args);

    // Add screen_settings filter
    add_filter('screen_settings', 'ctrw_screen_settings_html', 10, 2);
}

function ctrw_screen_settings_html($settings, $screen) {
    if ($screen->id !== 'toplevel_page_customer-reviews') {
        return $settings;
    }

    $user_id = get_current_user_id();
    $saved = get_user_meta($user_id, 'ctrw_column_visibility', true);
    $defaults = [
        'review-title' => 1,
        'author' => 1,
        'rating' => 1,
        'review' => 1,
        'admin-reply' => 1,
        'status' => 1,
        'action' => 1,
    ];
    $columns = is_array($saved) ? array_merge($defaults, $saved) : $defaults;

    ob_start();
    ?>
    <fieldset class="options-group">
        <legend><?php esc_html_e('Show/Hide Columns', 'wp_cr'); ?></legend>
        <?php foreach ($defaults as $col => $def): ?>
            <label style="margin-right:10px;">
                <input type="checkbox" class="ctrw-toggle-col" data-col="<?php echo esc_attr($col); ?>" <?php checked($columns[$col]); ?>>
                <?php
                $labels = [
                    'review-title' => 'Review Title',
                    'author' => 'Author',
                    'rating' => 'Rating',
                    'review' => 'Review',
                    'admin-reply' => 'Admin Reply',
                    'status' => 'Status',
                    'action' => 'Action'
                ];
                echo esc_html($labels[$col]);
                ?>
            </label>
        <?php endforeach; ?>
    </fieldset>
    <script>
    (function($){
        function ctrw_toggle_columns() {
            $('.ctrw-toggle-col').each(function(){
                var col = $(this).data('col');
                var checked = $(this).is(':checked');
                var idx = {
                    'review-title': 2,
                    'author': 3,
                    'rating': 4,
                    'review': 5,
                    'admin-reply': 6,
                    'status': 7,
                    'action': 8
                }[col];
                if (idx) {
                    $('.wp-list-table th:nth-child(' + idx + '), .wp-list-table td:nth-child(' + idx + ')')
                        .toggle(checked);
                }
            });
        }

        $(document).ready(function(){
            $('.ctrw-toggle-col').on('change', function(){
                ctrw_toggle_columns();
                var data = {};
                $('.ctrw-toggle-col').each(function(){
                    data[$(this).data('col')] = $(this).is(':checked') ? 1 : 0;
                });
                $.post(ajaxurl, {
                    action: 'ctrw_save_column_visibility',
                    columns: data,
                    _wpnonce: '<?php echo wp_create_nonce('ctrw_save_cols'); ?>'
                });
            });

            // Initial run
            ctrw_toggle_columns();
        });
    })(jQuery);
    </script>
    <?php
    return $settings . ob_get_clean();
}

add_filter('set-screen-option', 'ctrw_save_screen_option', 10, 3);
function ctrw_save_screen_option($status, $option, $value) {
    if ($option === 'ctrw_reviews_per_page') {
        return $value;
    }
    return $status;
}

add_action('wp_ajax_ctrw_save_column_visibility', 'ctrw_save_column_visibility');
function ctrw_save_column_visibility() {
    check_ajax_referer('ctrw_save_cols');
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Permission denied');
    }
    $columns = isset($_POST['columns']) && is_array($_POST['columns']) ? $_POST['columns'] : [];
    $user_id = get_current_user_id();
    update_user_meta($user_id, 'ctrw_column_visibility', $columns);
    wp_send_json_success();
}






add_filter('woocommerce_product_tabs', 'replace_reviews_tab_with_custom_plugin');
function replace_reviews_tab_with_custom_plugin($tabs) {
    $setting = get_option('customer_reviews_settings');
    if(empty($setting['replace_woocommerce_reviews'])) {
        return $tabs; // Return early if the setting is not enabled
    }
    // Remove default WooCommerce Reviews tab
    unset($tabs['reviews']);

    // Add your custom reviews tab
    $tabs['custom_reviews'] = array(
        'title'    => __('Customer Reviews', 'your-plugin-textdomain'),
        'priority' => 30,
        'callback' => 'render_custom_reviews_tab_content'
    );

    return $tabs;
}

function render_custom_reviews_tab_content() {


    
    echo '<div class="reviews_tab active" id="tab-title-reviews>';
    // Render your plugin's form and list via shortcode
    echo do_shortcode('[wp_ctrw_lists]');
    echo do_shortcode('[wp_ctrw_form]');

    echo '</div>';
}


add_action('init', function () {
    $setting = get_option('customer_reviews_settings');
    if(empty($setting['replace_woocommerce_reviews'])) {
        return ; // Return early if the setting is not enabled
    }
    update_option('woocommerce_enable_reviews', 'no');
    update_option('woocommerce_enable_review_rating', 'no');
    update_option('woocommerce_review_rating_required', 'no');
    update_option('woocommerce_review_rating_verification_label', 'no');
    update_option('woocommerce_review_rating_verification_required', 'no');
});


register_uninstall_hook(__FILE__, 'wp_customer_reviews_uninstall');
function wp_customer_reviews_uninstall() {
    global $wpdb;

    // Delete the custom reviews table
    $table_name = $wpdb->prefix . 'customer_reviews';
    $wpdb->query("DROP TABLE IF EXISTS $table_name");

    // Remove plugin options
    delete_option('customer_reviews_settings');

    delete_option('woocommerce_enable_reviews');
    delete_option('woocommerce_enable_review_rating');
    delete_option('woocommerce_review_rating_required');
    delete_option('woocommerce_review_rating_verification_label');
    delete_option('woocommerce_review_rating_verification_required');
    delete_option('customer_reviews_settings');

    

    // Remove user meta for column visibility
    $users = get_users(['fields' => 'ID']);
    foreach ($users as $user_id) {
        delete_user_meta($user_id, 'ctrw_column_visibility');
    }

    delete_post_meta_by_key('_ctrw_enable_reviews');
    delete_post_meta_by_key('_ctrw_enable_review_form');
    delete_post_meta_by_key('_ctrw_enable_review_list');
}



/**
 * 1. Ensure WooCommerce Reviews Tab Exists
 */
add_filter('woocommerce_product_tabs', 'ctrw_force_reviews_tab', 10);
function ctrw_force_reviews_tab($tabs) {
    $woocommerceSettings = get_option('ctrw_woocommerce_settings', array());
    if(isset($woocommerceSettings['show_on_product_pages']) && $woocommerceSettings['show_on_product_pages'] == 'on'){
      
    

    global $post;
    
    if (!is_product()) return $tabs;
    
    // Always show Reviews tab, even with 0 reviews
    $tabs['reviews'] = array(
        'title'    => __('Reviews', 'woocommerce'),
        'priority' => 30,
        'callback' => 'ctrw_custom_reviews_tab_content'
    );
    }
    
    return $tabs;
}

/**
 * 2. Custom Reviews Tab Content
 */
function ctrw_custom_reviews_tab_content() {

   $woocommerceSettings = get_option('customer_reviews_settings', array());

   
   if(isset($woocommerceSettings['review_summary']) && $woocommerceSettings['review_summary'] == 'on'){
     echo do_shortcode('[wp_ctrw_summary]');
   }
   
   if(isset($woocommerceSettings['review_display_type']) && $woocommerceSettings['review_display_type'] == 'slider'){
        echo do_shortcode('[wp_ctrw_slider]');
   }elseif(isset($woocommerceSettings['review_display_type']) && $woocommerceSettings['review_display_type'] == 'floating'){
        echo do_shortcode('[wp_ctrw_widget]');
   }else{
        echo do_shortcode('[wp_ctrw_lists]');
   }
  
}