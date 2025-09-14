<div class="wrap wp-review-settings-wrap">
    <h1>Customer Review Settings</h1>

    
 
    <h2 class="nav-tab-wrapper">
        <a href="#" id="ctrw-general-tab" class="nav-tab nav-tab-active" onclick="showTab(event, 'general')">General</a>
        <a href="#" id="ctrw-review-form-tab"class="nav-tab " onclick="showTab(event, 'review_form')">Review Form Settings</a>
        <a href="#" id="ctrw-shortcodes-tab"class="nav-tab " onclick="showTab(event, 'display')">Shortcodes</a>
        <a href="#" id="ctrw-schema-tab" class="nav-tab " onclick="showTab(event, 'schema')">Schema Settings</a>
        <a href="#" id="ctrw-advanced-tab" class="nav-tab " onclick="showTab(event, 'advanced')">Advanced Settings</a>

    </h2>

    <!-- Success message -->
    <div id="ctrw-success-msg" class="notice notice-success is-dismissible" style="display: none;">
        <p>Customer Review Settings Saved Successfully.</p>
    </div>

    <form method="post"  id="ctrw-form-settings" class="wp-review-settings-form">
        <!-- General Settings -->
        <div class="form-group tab-section" id="tab-general" >
            <h3><?php esc_html_e('General Settings', 'wp_cr'); ?></h3>
            <div style="display: flex; flex-wrap: wrap; gap: 24px;">
                <div style="flex: 1 1 0; min-width: 260px;">
                    <label for="reviews_per_page"><?php esc_html_e('Reviews shown per page:', 'wp_cr'); ?></label>
                    <input type="number" name="reviews_per_page" id="reviews_per_page" 
                        value="<?= esc_attr(get_option('customer_reviews_settings')['reviews_per_page'] ?? 12) ?>">

                        <label for="reviews_per_row_slder"><?php esc_html_e('Reviews per Row:', 'wp_cr'); ?></label>
                        <input type="number" name="reviews_per_row_slder" id="reviews_per_row_slder"
                            value="<?= esc_attr(get_option('customer_reviews_settings')['reviews_per_row_slder'] ?? 3) ?>" min="1" max="10">
                        <span class="ctrw-tooltip">
                            <span class="dashicons dashicons-editor-help"></span>
                            <span class="tooltiptext tooltip-right-msg"><?php esc_html_e('Number of reviews to display horizontally in the Slider.', 'wp_cr'); ?></span>
                        </span>

                    <label for="date_format"><?php esc_html_e('Date Format:', 'wp_cr'); ?></label>
                    <select name="date_format" id="date_format">
                        <option value="MM/DD/YYYY" <?= selected(get_option('customer_reviews_settings')['date_format'] ?? '', 'MM/DD/YYYY', false) ?>>MM/DD/YYYY</option>
                        <option value="DD/MM/YYYY" <?= selected(get_option('customer_reviews_settings')['date_format'] ?? '', 'DD/MM/YYYY', false) ?>>DD/MM/YYYY</option>
                        <option value="YYYY/MM/DD" <?= selected(get_option('customer_reviews_settings')['date_format'] ?? '', 'YYYY/MM/DD', false) ?>>YYYY/MM/DD</option>
                    </select>

                    <label for="include_time"><input type="checkbox" name="include_time" id="include_time" value="1" 
                    <?= checked(1, get_option('customer_reviews_settings')['include_time'] ?? 0, false) ?>> 
                    <?php esc_html_e('Include Time', 'wp_cr'); ?>
                    <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">Display a time stamp behind the date on each comment.</span>
				    </span>
                    
                
                   </label>
                    <label for="enable_email_notification"><input type="checkbox" name="enable_email_notification" id="enable_email_notification" value="1"
                    <?= checked(1, get_option('customer_reviews_settings')['enable_email_notification'] ?? 1, false) ?>> 
                      <?php esc_html_e('Enable Admin Email Notification', 'wp_cr'); ?>
                    <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">Email(s) designated under 'Advanced Settings' tab will receive a notification from each review entered in the plugins form.</span>
				    </span>
                    </label>
                  
                    <label for="enable_customer_email_notification"><input type="checkbox" name="enable_customer_email_notification" id="enable_customer_email_notification" value="1"
                    <?= checked(1, get_option('customer_reviews_settings')['enable_customer_email_notification'] ?? 0, false) ?>> 
                     <?php esc_html_e('Enable Customer Email Receipt', 'wp_cr'); ?>
                    <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">Users receive a receipt from their form submission</span>
				    </span>
                   </label>
                    <label for="auto_approve_reviews">
                        <input type="checkbox" name="auto_approve_reviews" id="auto_approve_reviews" value="1"
                        <?= checked(1, get_option('customer_reviews_settings')['auto_approve_reviews'] ?? 0, false) ?>>
                      <?php esc_html_e('Enable Automatic Review Approval', 'wp_cr'); ?>
                        <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">New reviews are immediately posted for viewing without admin review.</span>
				    </span>
                       
                    </label>
                    <label for="show_city">
                        <input type="checkbox" name="show_city" id="show_city" value="1"
                        <?= checked(1, get_option('customer_reviews_settings')['show_city'] ?? 0, false) ?>>
                     <?php esc_html_e('Show City in Review List', 'wp_cr'); ?>
                        <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">This option allows the city to be displayed with each comment.</span>
				    </span>
                    
                    </label>
                    <label for="show_state">
                        <input type="checkbox" name="show_state" id="show_state" value="1"
                        <?= checked(1, get_option('customer_reviews_settings')['show_state'] ?? 0, false) ?>>
          <?php esc_html_e('Show State in Review List', 'wp_cr'); ?>
                        <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">This option allows the state to be displayed with each comment.</span>
				    </span>
                      
                    </label>    <label for="enable_review_title">
                            <input type="checkbox" name="enable_review_title" id="enable_review_title" value="1"
                            <?= checked(1, get_option('customer_reviews_settings')['enable_review_title'] ?? 1, false) ?>>
                               <?php esc_html_e('Enable Review Title', 'wp_cr'); ?>
                            <span class="ctrw-tooltip">
					<span class="dashicons dashicons-editor-help"></span>
					<span class="tooltiptext tooltip-right-msg">This option allows the review title to be displayed with each comment.</span>
				    </span>
                         
                        </label>

                    <label for="name_font_weight"><?php esc_html_e('Name Font Weight:', 'wp_cr'); ?></label>
                    <select name="name_font_weight" id="name_font_weight">
                        <option value="normal" <?= selected(get_option('customer_reviews_settings')['name_font_weight'] ?? '', 'normal', false) ?>>Normal</option>
                        <option value="bold" <?= selected(get_option('customer_reviews_settings')['name_font_weight'] ?? '', 'bold', false) ?>>Bold</option>
                    </select>
                    
                
                </div>
                <div style="flex: 1 1 0; min-width: 260px;">
                    

                   

                    <label for="comment_font_size"><?php esc_html_e('Comment Font Size In Pixels:', 'wp_cr'); ?></label>
                    <input type="number" name="comment_font_size" id="comment_font_size" 
                        value="<?= esc_attr(get_option('customer_reviews_settings')['comment_font_size'] ?? 14) ?>" min="1">
                    
                    
                    <label for="comment_line_height"><?php esc_html_e('Comment Line Height In Pixels:', 'wp_cr'); ?></label>
                    <input type="number" step="0.1" min="1" name="comment_line_height" id="comment_line_height"
                        value="<?= esc_attr(get_option('customer_reviews_settings')['comment_line_height'] ?? 23) ?>">

        <label for="comment_font_style"><?php esc_html_e('Comment Font Style:', 'wp_cr'); ?></label>
                    <select name="comment_font_style" id="comment_font_style">
                        <option value="normal" <?= selected(get_option('customer_reviews_settings')['comment_font_style'] ?? '', 'normal', false) ?>>Normal</option>
                        <option value="italic" <?= selected(get_option('customer_reviews_settings')['comment_font_style'] ?? '', 'italic', false) ?>>Italic</option>
                    </select>

                    <label for="comment_box_fill_color"><?php esc_html_e('Comment Box Fill Color:', 'wp_cr'); ?></label>
                    <?php
                    // Enqueue WordPress color picker scripts/styles
                    if (function_exists('wp_enqueue_style')) {
                        wp_enqueue_style('wp-color-picker');
                        wp_enqueue_script('wp-color-picker');
                    }
                    $comment_box_fill_color = get_option('customer_reviews_settings')['comment_box_fill_color'] ?? '#f5f5f5';
                    ?>
                    <input type="text" name="comment_box_fill_color" id="comment_box_fill_color"
                        value="<?= esc_attr($comment_box_fill_color) ?>" class="wp-color-picker-field" data-default-color="#f5f5f5">

                    <label for="star_color"><?php esc_html_e('Star Color:', 'wp_cr'); ?></label>
                    <?php
                    $star_color = get_option('customer_reviews_settings')['star_color'] ?? '#fbbc04';
                    ?>
                    <input type="text" name="star_color" id="star_color"
                        value="<?= esc_attr($star_color) ?>" class="wp-color-picker-field" data-default-color="#fbbc04">
                    <script>
                    jQuery(document).ready(function($){
                        $('#star_color').wpColorPicker();
                        $('#comment_box_fill_color').wpColorPicker();
                    });
                    </script>



                </div>
            </div>
        </div>

       <!-- Review Form Settings -->
        <div class="tab-section" id="tab-review_form" style="display:none">
            <h3><?php esc_html_e('Review Form Fields Settings', 'wp_cr'); ?></h3>
            
            <div class="ctrw-settings-fields-grid" style="display: flex; gap: 32px;">
                <?php 
                $fields = ['Name', 'Email', 'Website', 'Phone', 'City', 'State', 'Review Title', 'Comment', 'Rating'];
                $fields_col1 = array_slice($fields, 0, 6);
                $fields_col2 = array_slice($fields, 6, 4);
                ?>
                <div style="flex:1; min-width:220px;">
                    <?php foreach ($fields_col1 as $field): 
                        $settings = get_option('customer_reviews_settings')['fields'][$field] ?? [];
                    ?>
                        <div class="ctrw-settings-field-row">
                            <input type="text" 
                                name="fields[<?= esc_attr($field) ?>][label]" 
                                value="<?= esc_attr($settings['label'] ?? $field) ?>" 
                                placeholder="<?= esc_attr($field) ?>"
                                class="ctrw-settings-field-input">
                            <div class="ctrw-settings-field-options">
                                <label class="ctrw-settings-option">
                                    <input type="checkbox" 
                                        name="fields[<?= esc_attr($field) ?>][require]" 
                                        value="1"
                                        <?= checked(1, $settings['require'] ?? 0, false) ?>>
                                    <span>Required</span>
                                </label>
                                <label class="ctrw-settings-option">
                                    <input type="checkbox" 
                                        name="fields[<?= esc_attr($field) ?>][show]" 
                                        value="1"
                                        <?= checked(1, $settings['show'] ?? 0, false) ?>>
                                    <span>Show</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div style="flex:1; min-width:220px;">
                    <?php foreach ($fields_col2 as $field): 
                        $settings = get_option('customer_reviews_settings')['fields'][$field] ?? [];
                    ?>
                        <div class="ctrw-settings-field-row">
                            <input type="text" 
                                name="fields[<?= esc_attr($field) ?>][label]" 
                                value="<?= esc_attr($settings['label'] ?? $field) ?>" 
                                placeholder="<?= esc_attr($field) ?>"
                                class="ctrw-settings-field-input">
                            <div class="ctrw-settings-field-options">
                                <label class="ctrw-settings-option">
                                    <input type="checkbox" 
                                        name="fields[<?= esc_attr($field) ?>][require]" 
                                        value="1"
                                        <?= checked(1, $settings['require'] ?? 0, false) ?>>
                                    <span>Required</span>
                                </label>
                                <label class="ctrw-settings-option">
                                    <input type="checkbox" 
                                        name="fields[<?= esc_attr($field) ?>][show]" 
                                        value="1"
                                        <?= checked(1, $settings['show'] ?? 0, false) ?>>
                                    <span>Show</span>
                                </label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Shortcodes -->
        <div class="form-group tab-section" id="tab-display" style="display:none">
            <h3><?php esc_html_e('Shortcodes', 'wp_cr'); ?></h3>
            <div class="shortcode-section">
                <label for="shortcode">Reviews Form:</label>
                <input type="text" id="shortcode" value="[wp_ctrw_form]" readonly>
                <button type="button" class="copy-button" onclick="navigator.clipboard.writeText('[wp_ctrw_form]')">Copy</button>
            </div>
            <div class="shortcode-section">
                <label for="shortcode">Reviews Summary:</label>
                <input type="text" id="shortcode" value="[wp_ctrw_summary]" readonly>
                <button type="button" class="copy-button" onclick="navigator.clipboard.writeText('[wp_ctrw_summary]')">Copy</button>
            </div>
            <div class="shortcode-section">
                <label for="shortcode">Reviews List Widget:</label>
                <input type="text" id="shortcode" value="[wp_ctrw_lists]" readonly>
                <button type="button" class="copy-button" onclick="navigator.clipboard.writeText('[wp_ctrw_lists]')">Copy</button>
            </div>
            <div class="shortcode-section">
                <label for="shortcode">Reviews Slider Widget:</label>
                <input type="text" id="shortcode" value="[wp_ctrw_slider]" readonly>
                <button type="button" class="copy-button" onclick="navigator.clipboard.writeText('[wp_ctrw_slider]')">Copy</button>
            </div>
                        <div class="shortcode-section">
                <label for="shortcode">Reviews Floating Widget:</label>
                <input type="text" id="shortcode" value="[wp_ctrw_widget]" readonly>
                <button type="button" class="copy-button" onclick="navigator.clipboard.writeText('[wp_ctrw_widget]')">Copy</button>
            </div>
        </div>


        <div class="form-group tab-section" id="tab-schema" style="display:none">
            <h3><?php esc_html_e('Schema Settings', 'wp_cr'); ?></h3>
            <?php
            $schemaSettings = get_option('customer_reviews_settings');
            ?>
            <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="business_name">Enabled Schema markup</label></th>
                    <td>
                        <input type="checkbox" name="enabled_schema" <?php checked(isset($schemaSettings['enabled_schema']) ? $schemaSettings['enabled_schema'] : '', '1'); ?>>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="business_name">Local Business Name</label></th>
                    <td>
                        <input type="text" id="business_name" name="business_name" class="regular-text" placeholder="Your Business Name" value="<?php echo isset($schemaSettings['business_name']) ? esc_attr($schemaSettings['business_name']) : esc_attr(get_bloginfo('name')); ?>">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="default_description">Default Description</label></th>
                    <td>
                        <input type="text" id="default_description" name="default_description" class="regular-text" placeholder="Enter default description" value="<?php echo isset($schemaSettings['default_description']) ? esc_attr($schemaSettings['default_description']) : esc_attr(get_bloginfo('description')); ?>">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="default_url">Default URL</label></th>
                    <td>
                        <input type="text" id="default_url" name="default_url" class="regular-text" placeholder="Enter default URL" value="<?php echo isset($schemaSettings['default_url']) ? esc_attr($schemaSettings['default_url']) : esc_url(home_url('/')); ?>">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="default_image">Default Image</label></th>
                    <td>
                            <div class="image-upload-wrapper">
                                <div class="image-preview-wrapper">
                                    <?php 
                                    $fallback_image = '';
                                    if (!empty($schemaSettings['custom_image_url'])) {
                                        $image_url = esc_url($schemaSettings['custom_image_url']);
                                    } else {
                                        $site_icon_id = get_option('site_icon');
                                        if ($site_icon_id) {
                                            $image_url = esc_url(wp_get_attachment_image_url($site_icon_id, 'thumbnail'));
                                            $fallback_image = $image_url;
                                        } else {
                                            $image_url = '';
                                        }
                                    }
                                    ?>
                                    <img id="image-preview" src="<?php echo $image_url; ?>" height="100" <?php echo empty($image_url) ? 'style="display: none;"' : ''; ?>>
                                </div>
                                <input id="upload_image_button" type="button" class="button" value="Upload Image" />
                                <input type="hidden" name="custom_image_url" id="custom_image_url" value="<?php echo isset($schemaSettings['custom_image_url']) ? esc_attr($schemaSettings['custom_image_url']) : esc_attr($fallback_image); ?>">
                                <p class="description">Upload an image or it will use the site icon by default</p>
                            </div>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="business_address">Address</label></th>
                    <td>
                        <textarea id="business_address" name="business_address" class="regular-text" style="height: 80px;"><?php echo isset($schemaSettings['business_address']) ? esc_textarea($schemaSettings['business_address']) : ''; ?></textarea>
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="business_phone">Telephone Number</label></th>
                    <td>
                        <input type="text" id="business_phone" name="business_phone" class="regular-text" value="<?php echo isset($schemaSettings['business_phone']) ? esc_attr($schemaSettings['business_phone']) : ''; ?>">
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"><label for="price_range">Price Range</label></th>
                    <td>
                        <select id="price_range" name="price_range" class="regular-text">
                            <option value="$" <?php selected(isset($schemaSettings['price_range']) ? $schemaSettings['price_range'] : '', '$'); ?>>$</option>
                            <option value="$$" <?php selected(isset($schemaSettings['price_range']) ? $schemaSettings['price_range'] : '', '$$'); ?>>$$</option>
                            <option value="$$$" <?php selected(isset($schemaSettings['price_range']) ? $schemaSettings['price_range'] : '', '$$$'); ?>>$$$</option>
                            <option value="$$$$" <?php selected(isset($schemaSettings['price_range']) ? $schemaSettings['price_range'] : '', '$$$$'); ?>>$$$$</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>

        </div>

        <div class="form-group tab-section" id="tab-advanced" style="display:none">
            <h3><?php esc_html_e('Advanced Settings', 'wp_cr'); ?></h3>
            <label for="replace_woocommerce_reviews">
                            <input type="checkbox" name="replace_woocommerce_reviews" id="replace_woocommerce_reviews" value="1"
                            <?= checked(1, get_option('customer_reviews_settings')['replace_woocommerce_reviews'] ?? 0, false) ?>>
                            <?php esc_html_e('Replace WooCommerce Default Review System', 'wp_cr'); ?>
                        </label>


            <label for="review_display_type"><?php esc_html_e('WooCommerce Reviews Display Style:', 'wp_cr'); ?></label>
                        <select name="review_display_type" id="review_display_type">
                            <option value="list" <?= selected(get_option('customer_reviews_settings')['review_display_type'] ?? 'list', 'list', false) ?>><?php esc_html_e('List', 'wp_cr'); ?></option>
                            <option value="slider" <?= selected(get_option('customer_reviews_settings')['review_display_type'] ?? '', 'slider', false) ?>><?php esc_html_e('Slider', 'wp_cr'); ?></option>
                            <option value="floating" <?= selected(get_option('customer_reviews_settings')['review_display_type'] ?? '', 'floating', false) ?>><?php esc_html_e('Floating Widget', 'wp_cr'); ?></option>

                        </select><br> <p id="review_display_info"></p>

            <div style="margin-top: 18px;">
                <label for="notification_admin_emails">
                    <?php esc_html_e('Notification Admin Emails', 'wp_cr'); ?>
                    <span class="ctrw-tooltip">
                        <span class="dashicons dashicons-editor-help"></span>
                        <span class="tooltiptext tooltip-right-msg">If more than one email, separate with a comma</span>
                    </span>
                </label>
                <?php
                    $settings = get_option('customer_reviews_settings');
                    $admin_email = get_option('admin_email');
                    $emails = isset($settings['notification_admin_emails']) && trim($settings['notification_admin_emails']) !== ''
                        ? $settings['notification_admin_emails']
                        : $admin_email;
                ?>
                <input type="text" name="notification_admin_emails" id="notification_admin_emails"
                    value="<?= esc_attr($emails); ?>"
                    placeholder="<?= esc_attr($admin_email); ?>"
                    style="width: 100%;">
            </div>

        </div>



        
        
        <p class="submit">
            <button type="submit" class="button-primary">Save Settings</button>
        </p>    
    </form>
</div>

<script>
function showTab(e, tabId) {
    e.preventDefault();
    document.querySelectorAll('.tab-section').forEach(tab => tab.style.display = 'none');
    document.querySelectorAll('.nav-tab').forEach(tab => tab.classList.remove('nav-tab-active'));
    document.getElementById('tab-' + tabId).style.display = 'block';
    e.currentTarget.classList.add('nav-tab-active');
}

</script>
