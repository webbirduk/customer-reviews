<?php
// ctrw-import-popup.php

if (!defined('ABSPATH')) {
      exit; // Exit if accessed directly
}
?>

<div class="ctrw-import-popup" id="ctrw-import-popup" style="display:none; position:fixed; top:50%; left:50%; transform:translate(-50%, -50%);
      background:#fff; padding:20px; box-shadow:0 0 10px rgba(0,0,0,0.5); z-index:1000;">
      <h2><?php esc_html_e('Import Customer Reviews', 'ctrw-reviews'); ?></h2>
      <form id="ctrw-import-form">
            <?php wp_nonce_field('ctrw_import_reviews', 'ctrw_import_nonce'); ?>
            <p>
                    <label for="ctrw_import_plugin"><?php esc_html_e('Select Review Plugin:', 'ctrw-reviews'); ?></label><br>
                    <select name="ctrw_import_plugin" id="ctrw_import_plugin" required>
                              <option value="">-- Select Plugin --</option>


                              <?php if (is_plugin_active('site-reviews/site-reviews.php')) : ?>
                                    <option value="siteReviews"><?php esc_html_e('Site Reviews', 'ctrw-reviews'); ?></option>
                              <?php endif; ?>

                              <?php if (is_plugin_active('wp-customer-reviews/wp-customer-reviews-3.php')) : ?>
                                    <option value="wpCustomerReviews"><?php esc_html_e('WP   Reviews', 'ctrw-reviews'); ?></option>
                              <?php endif; ?>
                             
                    </select>
            </p>
            
            <p>
                  
                  <input type="submit" class="button button-primary" value="Import Reviews">
                  <button type="button" id="close-ctrw-import-popup" class="button button-danger" ><?php esc_html_e('Close', 'ctrw-reviews'); ?></button>
            </p>
      </form>
      <div id="ctrw-import-result"></div>
</div>