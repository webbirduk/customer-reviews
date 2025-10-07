<?php
if (!defined('ABSPATH')) {
    exit;
}

class Review_Controller {
    private $model;
    private $view;

    public function __construct() {
        $this->model = new CTRW_Review_Model();
        $this->view = new Review_View();
        
        // Initialize hooks
        $this->initialize_hooks();
    }

    private function initialize_hooks() {
        // Admin hooks
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this,'wp_review_admin_styles']);
        add_action('load-toplevel_page_customer-reviews', [$this, 'add_screen_options']);
        add_action('wp_ajax_ctrw_save_column_visibility', [$this, 'save_column_visibility']);

        // Plugin list page links
        add_filter('plugin_action_links_' . CTRW_BASE_NAME, [$this, 'ctrw_plugin_action_links']);
        add_filter('plugin_row_meta', [$this, 'add_ctrw_description_link'], 10, 2);
        add_filter('plugin_row_meta', [$this, 'add_ctrw_details_link'], 10, 4);

        // Frontend hooks
        add_action('wp_enqueue_scripts', [$this,'review_enqueue_scripts']);
        add_action('wp_head', [$this, 'ctrw_output_schema_markup']);

        // WooCommerce Integration Hooks
        add_action('woocommerce_after_shop_loop_item_title', [$this, 'ctrw_display_product_review_info'], 15);
        add_action('woocommerce_single_product_summary', [$this, 'ctrw_display_product_review_info'], 7);
        add_filter('woocommerce_product_tabs', [$this, 'replace_reviews_tab_with_custom_plugin']);
        add_action('init', [$this, 'disable_default_woocommerce_reviews']);

        // AJAX hooks
        add_action('wp_ajax_ctrw_save_settings', [$this, 'ctrw_save_settings']);
        add_action('wp_ajax_submit_review', [$this, 'submit_review']);
        add_action('wp_ajax_nopriv_submit_review', [$this, 'submit_review']);
        add_action('wp_ajax_save_review_reply', [$this, 'save_review_reply']);
        add_action('wp_ajax_nopriv_save_review_reply', [$this, 'save_review_reply']);
        add_action('wp_ajax_edit_customer_review', [$this, 'edit_customer_review']);
        add_action('wp_ajax_nopriv_edit_customer_review', [$this, 'edit_customer_review']);
        add_action('wp_ajax_ctrw_import_review_from_others', [$this, 'ctrw_import_reviews']);
        add_action('wp_ajax_nopriv_ctrw_import_review_from_others', [$this, 'ctrw_import_reviews']);
        add_action('wp_ajax_load_reviews_ajax', [$this, 'load_reviews_ajax_handler']);
        add_action('wp_ajax_nopriv_load_reviews_ajax', [$this, 'load_reviews_ajax_handler']);

        // Shortcodes and other hooks
        add_shortcode('wp_ctrw_form', [$this,'customer_reviews_form_shortcode']);
        add_shortcode('wp_ctrw_summary', [$this,'ctrw_display_summary']);
        add_shortcode('wp_ctrw_lists', [$this,'customer_reviews_list_shortcode']);
        add_shortcode('wp_ctrw_widget', [$this,'ctrw_display_floating_widget']);
        add_shortcode('wp_ctrw_slider', [$this,'ctrw_display_slider']);
        add_filter('set-screen-option', [$this, 'save_screen_option'], 10, 3);
    }
    
    /**
     * Plugin Activation and Uninstallation - MUST BE STATIC
     */
    public static function activate() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'customer_reviews';
        if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name) {
            return; 
        }

        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        $sql = "CREATE TABLE $table_name (
            id INT(11) NOT NULL AUTO_INCREMENT,
            title VARCHAR(255) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(25) DEFAULT NULL,
            website VARCHAR(255) DEFAULT NULL,
            rating INT(1) NOT NULL,
            comment TEXT NOT NULL,
            city VARCHAR(255) DEFAULT NULL,
            state VARCHAR(255) DEFAULT NULL,
            status VARCHAR(20) DEFAULT 'pending',
            positionid INT(11) DEFAULT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            admin_reply TEXT DEFAULT NULL,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";

        dbDelta($sql);
    }
    
    public static function uninstall() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'customer_reviews';
        $wpdb->query("DROP TABLE IF EXISTS $table_name");

        // Remove plugin options
        delete_option('customer_reviews_settings');
        delete_option('woocommerce_enable_reviews');
        delete_option('woocommerce_enable_review_rating');
        delete_option('woocommerce_review_rating_required');
        delete_option('woocommerce_review_rating_verification_label');
        delete_option('woocommerce_review_rating_verification_required');

        // Remove user meta for screen options
        $users = get_users(['fields' => 'ID']);
        foreach ($users as $user_id) {
            delete_user_meta($user_id, 'ctrw_column_visibility');
            delete_user_meta($user_id, 'ctrw_reviews_per_page');
        }

        // Remove post meta
        delete_post_meta_by_key('_ctrw_enable_reviews');
        delete_post_meta_by_key('_ctrw_enable_review_form');
        delete_post_meta_by_key('_ctrw_enable_review_list');
    }

    /**
     * Screen Options for the admin reviews table
     */
    public function add_screen_options() {
        $option = 'per_page';
        $args = [
            'label'   => 'Reviews per page',
            'default' => 10,
            'option'  => 'ctrw_reviews_per_page'
        ];
        add_screen_option($option, $args);
        add_filter('screen_settings', [$this, 'screen_settings_html'], 10, 2);
    }

    public function screen_settings_html($settings, $screen) {
        if ($screen->id !== 'toplevel_page_customer-reviews') {
            return $settings;
        }

        $user_id = get_current_user_id();
        $saved = get_user_meta($user_id, 'ctrw_column_visibility', true);
        $defaults = [
            'review-title' => 1, 'author' => 1, 'rating' => 1, 'review' => 1,
            'admin-reply' => 1, 'status' => 1, 'action' => 1,
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
                        'review-title' => 'Review Title', 'author' => 'Author', 'rating' => 'Rating',
                        'review' => 'Review', 'admin-reply' => 'Admin Reply', 'status' => 'Status', 'action' => 'Action'
                    ];
                    echo esc_html($labels[$col]);
                    ?>
                </label>
            <?php endforeach; ?>
        </fieldset>
        <?php
        return $settings . ob_get_clean();
    }
    
    public function save_screen_option($status, $option, $value) {
        if ($option === 'ctrw_reviews_per_page') {
            return $value;
        }
        return $status;
    }
    
    public function save_column_visibility() {
        check_ajax_referer('ctrw_nonce', 'security');
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied');
        }
        $columns = isset($_POST['columns']) && is_array($_POST['columns']) ? array_map('intval', $_POST['columns']) : [];
        update_user_meta(get_current_user_id(), 'ctrw_column_visibility', $columns);
        wp_send_json_success();
    }

    /**
     * WooCommerce Integration
     */
    public function replace_reviews_tab_with_custom_plugin($tabs) {
        $setting = get_option('customer_reviews_settings');
        if(empty($setting['replace_woocommerce_reviews'])) {
            return $tabs;
        }
        unset($tabs['reviews']);
        $tabs['custom_reviews'] = [
            'title'    => __('Customer Reviews', 'wp_cr'),
            'priority' => 30,
            'callback' => [$this, 'render_custom_reviews_tab_content']
        ];
        return $tabs;
    }

    public function render_custom_reviews_tab_content() {
        $settings = get_option('customer_reviews_settings');
        $display_type = $settings['review_display_type'] ?? 'list';
        
        echo '<div class="reviews_tab active" id="tab-title-reviews">';
        
        if ($display_type === 'slider') {
            echo do_shortcode('[wp_ctrw_slider]');
        } elseif ($display_type === 'floating') {
             echo do_shortcode('[wp_ctrw_widget]');
        } else {
            echo do_shortcode('[wp_ctrw_lists]');
        }
        
        echo do_shortcode('[wp_ctrw_form]');
        echo '</div>';
    }

    public function disable_default_woocommerce_reviews() {
        $setting = get_option('customer_reviews_settings');
        if(!empty($setting['replace_woocommerce_reviews'])) {
            update_option('woocommerce_enable_reviews', 'no');
            update_option('woocommerce_enable_review_rating', 'no');
        }
    }
    
    /**
     * Import reviews from other plugins.
     */
    public function ctrw_import_reviews() {
        $importPlugin = isset($_POST['ctrw_import_review']) ? sanitize_text_field($_POST['ctrw_import_review']) : '';
        
        if ($importPlugin == 'siteReviews') {
            $this->model->import_reviews_from_site_reviews_plugin();
        } elseif ($importPlugin == 'wpCustomerReviews') {
            $this->model->import_reviews_from_wp_customer_reviews();
        } else {
            wp_send_json_error(['message' => __('Please select a plugin to import from.', 'wp_cr')]);
        }
        
        wp_send_json_success(['message' => __('Reviews imported successfully.', 'wp_cr')]);
    }

    /**
     * Add links to the plugin list page
     */
    public function ctrw_plugin_action_links($links) {
        unset($links['edit']);
        $settings_link = '<a href="' . admin_url('admin.php?page=wp-review-settings') . '">' . __('Settings', 'wp_cr') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }

    public function add_ctrw_description_link($links, $file) {
        if (CTRW_BASE_NAME == $file) {
            $row_meta = [
                'donation' => '<a href="' . esc_url('https://www.zeffy.com/en-US/donation-form/your-donation-makes-a-difference-6') . '" target="_blank">' . esc_html__('Donation for Homeless', 'wp_cr') . '</a>'
            ];
            return array_merge($links, $row_meta);
        }
        return $links;
    }

    public function add_ctrw_details_link($links, $plugin_file, $plugin_data) {
        if (isset($plugin_data['PluginURI']) && strpos($plugin_data['PluginURI'], 'wordpress.org/plugins/customer-reviews') !== false) {
            $slug = basename($plugin_data['PluginURI']);
            unset($links[2]);
            $links[] = sprintf('<a href="%s" class="thickbox" title="%s">%s</a>', self_admin_url('plugin-install.php?tab=plugin-information&amp;plugin=' . $slug . '&amp;TB_iframe=true&amp;width=772&amp;height=563'), esc_attr(sprintf(__('More information about %s', 'wp_cr'), $plugin_data['Name'])), __('View Details', 'wp_cr'));
        }
        return $links;
    }

    /**
     * Admin Menu and Pages
     */
    public function add_admin_menu() {
        add_menu_page('Reviews', 'Reviews', 'manage_options', 'customer-reviews', [$this, 'display_reviews_page'], 'dashicons-star-filled');
        add_submenu_page('customer-reviews', 'Review Settings', 'Review Settings', 'manage_options', 'wp-review-settings', [$this, 'display_settings_page']);
    }

    public function display_settings_page() {
        include CTRW_PLUGIN_PATH . 'includes/views/admin/ctrw-settings-panel.php';
    }
    
    public function display_reviews_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['bulk_action']) && !empty($_POST['review_ids'])) {
            $this->handle_bulk_action();
        }
        $status = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : 'all';
        $reviews = $this->model->get_reviews_by_status($status);
        $counts = $this->model->get_review_counts();
        $this->view->display_reviews($reviews, $counts, $status);
    }

    private function handle_bulk_action() {
        $action = sanitize_text_field($_POST['bulk_action']);
        $review_ids = array_map('intval', $_POST['review_ids']);
        if (!empty($review_ids)) {
            switch($action) {
                case 'approve':
                    $this->model->update_review_status($review_ids, 'approved');
                    break;
                case 'reject':
                    $this->model->update_review_status($review_ids, 'reject');
                    break;
                case 'trash':
                    $this->model->update_review_status($review_ids, 'trash');
                    break;
                case 'delete_permanently':
                    $this->model->delete_reviews($review_ids);
                    break;
            }
        }
    }

    /**
     * Enqueue Scripts and Styles
     */
    public function review_enqueue_scripts() {
        wp_enqueue_script('review-script', CTRW_PLUGIN_ASSETS . 'js/review-script.js', ['jquery'], '1.0.2', true);
        wp_localize_script('review-script', 'ctrw_ajax', ['ajax_url' => admin_url('admin-ajax.php')]);
        wp_enqueue_style('ctrw-review-form', CTRW_PLUGIN_ASSETS . 'css/ctrw-form.css', [], '1.0.2');
        wp_enqueue_style('ctrw-reviews-list', CTRW_PLUGIN_ASSETS . 'css/ctrw-reviews-list.css', [], '1.0.2');
        wp_enqueue_style('ctrw-reviews-slider', CTRW_PLUGIN_ASSETS . 'css/ctrw-reviews-slider.css', [], '1.0.2');
        wp_enqueue_style('ctrw-reviews-floating', CTRW_PLUGIN_ASSETS . 'css/ctrw-reviews-floating.css', [], '1.0.2');
        
        $settings = get_option('customer_reviews_settings');
        $star_color = !empty($settings['star_color']) ? sanitize_hex_color($settings['star_color']) : '#fbbc04';
        $custom_css = ".rating label:hover, .rating label:hover ~ label, .rating input:checked ~ label { color: {$star_color} !important; } .star.filled { color: {$star_color}; }";
        wp_add_inline_style('ctrw-review-form', $custom_css);
    }
    
    public function wp_review_admin_styles($hook) {
        if ($hook !== 'toplevel_page_customer-reviews' && $hook !== 'reviews_page_wp-review-settings') {
            return;
        }
        wp_enqueue_style('wp-color-picker');
        wp_enqueue_style('wp-review-admin', CTRW_PLUGIN_ASSETS . 'css/ctrw-admin.css', [], '1.0.2');
        wp_enqueue_script('cr-admin-script', CTRW_PLUGIN_ASSETS . 'js/ctrw-admin.js', ['jquery', 'wp-color-picker'], '1.0.2', true);
        wp_localize_script('cr-admin-script', 'ctrw_admin_ajax', ['ajax_url' => admin_url('admin-ajax.php'), 'nonce' => wp_create_nonce('ctrw_nonce')]);
    }

    /**
     * AJAX Handlers
     */
    public function ctrw_save_settings() {
        check_ajax_referer('ctrw_nonce', 'security'); 
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
             $settings = [
                'enable_email_notification' => isset($_POST['enable_email_notification']) ? 1 : 0,
                'enable_customer_email_notification' => isset($_POST['enable_customer_email_notification']) ? 1 : 0,
                'auto_approve_reviews' => isset($_POST['auto_approve_reviews']) ? 1 : 0,
                'show_city' => isset($_POST['show_city']) ? 1 : 0,
                'show_state' => isset($_POST['show_state']) ? 1 : 0,
                'enable_review_title' => isset($_POST['enable_review_title']) ? 1 : 0,
                'name_font_weight' => sanitize_text_field($_POST['name_font_weight'] ?? 'normal'),
                'comment_font_size' => intval($_POST['comment_font_size'] ?? 14),
                'comment_line_height' => floatval($_POST['comment_line_height'] ?? 1.5),
                'comment_font_style' => sanitize_text_field($_POST['comment_font_style'] ?? 'normal'),
                'comment_box_fill_color' => sanitize_hex_color($_POST['comment_box_fill_color'] ?? '#f9f9f9'),
                'reviews_per_page' => intval($_POST['reviews_per_page'] ?? 10),
                'reviews_per_row_slder' => intval($_POST['reviews_per_row_slder'] ?? 3),
                'date_format' => sanitize_text_field($_POST['date_format'] ?? 'MM/DD/YYYY'),
                'include_time' => isset($_POST['include_time']) ? 1 : 0,
                'star_color' => sanitize_hex_color($_POST['star_color'] ?? '#fbbc04'),
                'enabled_schema' => isset($_POST['enabled_schema']) ? 1 : 0,
                'business_name' => sanitize_text_field($_POST['business_name'] ?? get_bloginfo('name')),
                'default_description' => sanitize_text_field($_POST['default_description'] ?? get_bloginfo('description')),
                'default_url' => esc_url_raw($_POST['default_url'] ?? home_url('/')),
                'custom_image_url' => esc_url_raw($_POST['custom_image_url'] ?? ''),
                'business_address' => sanitize_text_field($_POST['business_address'] ?? ''),
                'business_phone' => sanitize_text_field($_POST['business_phone'] ?? ''),
                'price_range' => sanitize_text_field($_POST['price_range'] ?? '$'),
                'review_display_type' => sanitize_text_field($_POST['review_display_type'] ?? 'list'),
                'replace_woocommerce_reviews' => isset($_POST['replace_woocommerce_reviews']) ? 1 : 0,
                'notification_admin_emails' => sanitize_text_field($_POST['notification_admin_emails'] ?? ''),   
                'fields' => isset($_POST['fields']) ? array_map(function($field) {
                    return [
                        'label' => sanitize_text_field($field['label']),
                        'require' => isset($field['require']) ? 1 : 0,
                        'show' => isset($field['show']) ? 1 : 0
                    ];
                }, $_POST['fields']) : []
            ];
            update_option('customer_reviews_settings', $settings);
            wp_send_json_success();
        }
        wp_send_json_error();
    }
     
    public function submit_review() {
        $data = array_map('sanitize_text_field', $_POST);
        $settings = get_option('customer_reviews_settings');
        $status = !empty($settings['auto_approve_reviews']) ? 'approved' : 'pending';
    
        $review_data = [
            'name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? '', 'website' => $data['website'] ?? '',
            'city' => $data['city'] ?? '', 'state' => $data['state'] ?? '', 'rating' => intval($data['rating'] ?? 0),
            'title' => $data['review_title'] ?? '', 'comment' => $data['comment'] ?? '', 'status' => $status,
            'positionid' => intval($data['positionid'] ?? 0)
        ];

        $this->model->ctrw_add_review($review_data);

        if (!empty($settings['enable_email_notification'])) {
            $this->notify_admin_of_pending_review($review_data);
        }
        if (!empty($settings['enable_customer_email_notification'])) {
            $this->notify_customer_of_pending_review($review_data['email'], $review_data['name'], $status);
        }

        wp_send_json_success(['message' => 'Review submitted successfully!']);
    }
    
    private function notify_customer_of_pending_review($email, $name, $status) {
        if (empty($email)) return;
        $subject = __('Thank you for your review', 'wp_cr');
        $message = sprintf(__("Thank you %s for your review! It is now currently %s.", 'wp_cr'), $name, $status);
        wp_mail($email, $subject, $message);
    }

    private function notify_admin_of_pending_review($review_data) {
        $settings = get_option('customer_reviews_settings');
        $admin_emails = !empty($settings['notification_admin_emails']) ? $settings['notification_admin_emails'] : get_option('admin_email');
        if (empty($admin_emails)) return;

        $subject = sprintf(__('New Customer Review from %s', 'wp_cr'), $review_data['name']);
        $site_name = get_bloginfo('name');
        
        ob_start();
        ?>
        <div style="font-family: Arial, sans-serif; font-size: 14px; line-height: 1.6;">
            <p>A new customer review has been submitted on <strong><?php echo esc_html($site_name); ?></strong>:</p>
            <table style="width: 100%; border-collapse: collapse;">
                <tr style="background-color: #f9f9f9;">
                    <td style="padding: 8px; border: 1px solid #ddd; width: 120px;"><strong>Name:</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo esc_html($review_data['name']); ?></td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Email:</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo esc_html($review_data['email']); ?></td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Rating:</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo intval($review_data['rating']); ?> out of 5</td>
                </tr>
                <tr>
                    <td style="padding: 8px; border: 1px solid #ddd;"><strong>Title:</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo esc_html($review_data['title']); ?></td>
                </tr>
                <tr style="background-color: #f9f9f9;">
                    <td style="padding: 8px; border: 1px solid #ddd; vertical-align: top;"><strong>Comment:</strong></td>
                    <td style="padding: 8px; border: 1px solid #ddd;"><?php echo nl2br(esc_html($review_data['comment'])); ?></td>
                </tr>
            </table>
            <p>You can manage this review in the <a href="<?php echo admin_url('admin.php?page=customer-reviews'); ?>">WordPress admin panel</a>.</p>
        </div>
        <?php
        $message = ob_get_clean();
        
        $headers = ['Content-Type: text/html; charset=UTF-8'];
        wp_mail($admin_emails, $subject, $message, $headers);
    }
    
    public function save_review_reply() {
        check_ajax_referer('ctrw_nonce', 'security');
        $id = intval($_POST['review_id']);
        $reply = sanitize_textarea_field($_POST['reply_message']);
        $this->model->update_review($id, ['admin_reply' => $reply]);
        wp_send_json_success(['reply' => $reply]);
    }
    
    public function edit_customer_review() {
        check_ajax_referer('ctrw_nonce', 'security');
        $id = intval($_POST['id']);
        $update_type = sanitize_text_field($_POST['update_type']);
        $data = [
            'name' => sanitize_text_field($_POST['name']), 'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']), 'website' => esc_url_raw($_POST['website']),
            'comment' => sanitize_textarea_field($_POST['comment']), 'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']), 'status' => sanitize_text_field($_POST['status']),
            'rating' => intval($_POST['rating']), 'title' => sanitize_text_field($_POST['title']),
            'positionid' => intval($_POST['positionid']),
        ];
        
        if ($update_type === 'add') {
            $this->model->ctrw_add_review($data);
        } else {
             $old_review = $this->model->get_review_by_id($id);
             if ($old_review && $old_review->status != $data['status']) {
                 $this->notify_customer_of_pending_review($data['email'], $data['name'], $data['status']);
             }
            $this->model->update_review($id, $data);
        }
        wp_send_json_success();
    }
    
    public function load_reviews_ajax_handler() {
        if (!defined('DOING_AJAX') || !DOING_AJAX) wp_die();

        $page = isset($_POST['page']) ? max(1, intval($_POST['page'])) : 1;
        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : get_queried_object_id();
        
        ob_start();
        include(CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-list.php');
        $content = ob_get_clean();
        
        echo $content;
        wp_die();
    }

    /**
     * Shortcode Callbacks
     */
    public function customer_reviews_form_shortcode() {
        ob_start();
        include CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-form.php';
        return ob_get_clean();
    }
    public function customer_reviews_list_shortcode() {
        ob_start();
        include CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-list.php';
        return ob_get_clean();
    }
    public function ctrw_display_summary() {
        ob_start();
        include CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-summary.php';
        return ob_get_clean();
    }
    public function ctrw_display_floating_widget() {
        ob_start();
        include CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-floating.php';
        return ob_get_clean();
    }
    public function ctrw_display_slider() {
        ob_start();
        include CTRW_PLUGIN_PATH . 'includes/views/shortcodes/ctrw-slider.php';
        return ob_get_clean();
    }

    /**
     * Display product review info on shop/product pages
     */
    public function ctrw_display_product_review_info() {
        global $post;
        if (!is_a($post, 'WP_Post') || $post->post_type !== 'product') return;

        $average_rating = $this->model->get_average_rating_by_positionid($post->ID);
        $review_count = $this->model->get_review_count_by_positionid($post->ID);
        
        if ($review_count > 0) {
            $star_color = get_option('customer_reviews_settings')['star_color'] ?? '#fbbc04';
            echo '<div class="ctrw-product-review-summary" style="margin: 5px 0 10px; font-size: 14px; display: flex; align-items: center;">';
            // Simple star display
            echo '<div class="ctrw-stars" style="color:'.esc_attr($star_color).'; margin-right: 5px;">';
            for ($i = 1; $i <= 5; $i++) {
                if ($i <= $average_rating) {
                    echo '&#9733;'; // filled star
                } else {
                    echo '&#9734;'; // empty star
                }
            }
            echo '</div>';
            echo '<a href="#reviews" class="woocommerce-review-link" rel="nofollow">(' . sprintf(_n('%s customer review', '%s customer reviews', $review_count, 'wp_cr'), '<span class="count">' . esc_html($review_count) . '</span>') . ')</a>';
            echo '</div>';
        }
    }
    
    /**
     * Output Schema Markup in the header
     */
    public function ctrw_output_schema_markup() {
        $schemaSettings = get_option('customer_reviews_settings');
        if (empty($schemaSettings['enabled_schema'])) return;

        $review_count = $this->model->get_review_count_by_status('approved');
        $average_rating = $this->model->get_average_rating();
        
        if($review_count == 0) return;

        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'LocalBusiness',
            'name' => $schemaSettings['business_name'] ?? get_bloginfo('name'),
            'image' => !empty($schemaSettings['custom_image_url']) ? esc_url($schemaSettings['custom_image_url']) : '',
            'telephone' => $schemaSettings['business_phone'] ?? '',
            'priceRange' => $schemaSettings['price_range'] ?? '',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => $schemaSettings['business_address'] ?? ''
            ],
            'aggregateRating' => [
                '@type' => 'AggregateRating',
                'ratingValue' => number_format($average_rating, 1),
                'reviewCount' => $review_count
            ],
        ];
        
        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . '</script>';
    }
}