<?php if (!defined('ABSPATH')) exit; ?>

<div class="customer-reviews-form-container">
    <b class="ctrw-form-heading"><?php esc_html_e('Submit Your Review', 'customer-reviews'); ?></b>
    <form id="customer-reviews-form">
        <?php 
        $fields = ['Name', 'Email', 'Website', 'Phone', 'City', 'State', 'Review Title', 'Comment', 'Rating'];
        $settings_data = get_option('customer_reviews_settings');
        $form_fields_settings = $settings_data['fields'] ?? [];

        foreach ($fields as $field): 
            
            // FIX: Convert field name to a valid snake_case key (e.g., "Review Title" -> "review_title")
            $field_key = strtolower(str_replace(' ', '_', $field));
            
            // Set defaults for fresh installs before settings are saved
            if (empty($form_fields_settings)) {
                $is_shown = 1;
                $is_required = in_array($field, ['Name', 'Email', 'Comment', 'Rating']); // Sensible defaults
                $label_name = $field;
            } else {
                $field_setting = $form_fields_settings[$field] ?? ['show' => 1, 'require' => 0];
                $is_shown = $field_setting['show'] ?? 0;
                $is_required = $field_setting['require'] ?? 0;
                $label_name = $field_setting['label'] ?? $field;
            }

            if ($is_shown): ?>
                <div class="form-group">
                    <label for="ctrw-<?= esc_attr($field_key) ?>"><?= esc_html($label_name) ?><?= $is_required ? ' <span class="required">*</span>' : '' ?></label>
                    <?php if ($field === 'Comment'): ?>
                        <textarea id="ctrw-<?= esc_attr($field_key) ?>" name="<?= esc_attr($field_key) ?>" <?= $is_required ? 'required' : '' ?>></textarea>
                   <?php elseif ($field === 'Rating'):?>
                        <div class="rating">
                            <input type="radio" id="star5" name="<?= esc_attr($field_key) ?>" value="5" <?= $is_required ? 'required' : '' ?>><label for="star5">★</label>
                            <input type="radio" id="star4" name="<?= esc_attr($field_key) ?>" value="4"><label for="star4">★</label>
                            <input type="radio" id="star3" name="<?= esc_attr($field_key) ?>" value="3"><label for="star3">★</label>
                            <input type="radio" id="star2" name="<?= esc_attr($field_key) ?>" value="2"><label for="star2">★</label>
                            <input type="radio" id="star1" name="<?= esc_attr($field_key) ?>" value="1"><label for="star1">★</label>
                        </div>
                    <?php else: 
                        $type = ($field === 'Email') ? 'email' : (($field === 'Website') ? 'url' : 'text');
                    ?>
                        <input id="ctrw-<?= esc_attr($field_key) ?>" type="<?= esc_attr($type) ?>" name="<?= esc_attr($field_key) ?>" <?= $is_required ? 'required' : '' ?>>
                    <?php endif; ?>
                </div>
            <?php endif; 
        endforeach; ?>
        
        <input type="hidden" name="positionid" value="<?php echo esc_attr(get_the_ID()); ?>">
        <input class="button-default" id="comment-submit" type="submit" value="<?php esc_attr_e('Submit', 'customer-reviews'); ?>">
    </form>
    <p id="review-message"></p>
</div>