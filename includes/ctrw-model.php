<?php
if (!defined('ABSPATH')) {
    exit;
}

class CTRW_Review_Model {

    private $wpdb;
    private $table;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table = $wpdb->prefix . 'customer_reviews';
    }
    public function get_reviews_by_status($status) {
        if ($status === 'all') {
            return $this->wpdb->get_results("SELECT * FROM $this->table");
        } else {
            return $this->wpdb->get_results($this->wpdb->prepare("SELECT * FROM $this->table WHERE status = %s", $status));
        }
    }

    public function get_review_counts() {

        $counts = [
            'all'      => 0,
            'approved' => 0,
            'reject'   => 0,
            'pending'  => 0,
            'trash'    => 0,
        ];

        $query = "SELECT status, COUNT(*) as count FROM $this->table GROUP BY status";
        $results = $this->wpdb->get_results($query);

        foreach ($results as $row) {
            $counts[$row->status] = $row->count;
        }

        $counts['all'] = array_sum($counts);

        return $counts;
    }

    function get_average_rating() {
        global $wpdb;
        
        // Base query
        $query = "SELECT AVG(rating) as average_rating FROM $this->table WHERE status = 'approved'";
        $result = $wpdb->get_var($query);
        
        return $result ? round($result, 1) : 0;
    }

    public function update_review_status($review_ids, $status) {

        $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE $this->table SET status = %s WHERE id IN (" . implode(',', array_map('intval', $review_ids)) . ")",
                $status
            )
        );
    }

    /**
     * Import reviews from the Site Reviews plugin.
     *
     * This function retrieves reviews from the Site Reviews plugin's database tables
     * and imports them into the custom reviews table.
     *
     * @return int The number of reviews imported.
     */

    public function import_reviews_from_site_reviews_plugin() {

        global $wpdb;
        // Get all 'site-review' posts from wp_posts
        $site_reviews = $wpdb->get_results(
            "SELECT ID, post_date, post_content, post_title
             FROM {$wpdb->posts} 
             WHERE post_type = 'site-review'"
        );

        $imported = 0;
        $data = [];
        if ($site_reviews) {
            foreach ($site_reviews as $review) {
                // Fetch name, rating, and email from wp_glsr_ratings for this review
                $glsr_rating = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT name, rating, email FROM {$wpdb->prefix}glsr_ratings WHERE review_id = %d LIMIT 1",
                        $review->ID
                    ),
                    ARRAY_A
                );



                // Merge fetched data into $data
                if ($glsr_rating) {
                    $data['name'] = $glsr_rating['name'];
                    $data['rating'] = $glsr_rating['rating'];
                    $data['email'] = $glsr_rating['email'];
                } else {
                    $data['name'] = '';
                    $data['rating'] = 0;
                    $data['email'] = '';
                }

                 $review_data = [
                    
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone' => '',
                    'website' => '',
                    'city' => '',
                    'state' => '',
                    'rating' => $data['rating'],
                    'title' => $review->post_title,
                    'comment' => $review->post_content,
                    'status' => 'approved',
                    'positionid' => '',
                    'created_at' => $review->post_date
                ];
                $imported++;
                $this->ctrw_add_review($review_data);
                
            }
        }
        
        return $imported;
    }

    public function import_reviews_from_wp_customer_reviews() {
        global $wpdb;

        // Get all review post IDs from wp_postmeta where meta_key = 'wpcr3_review_post'
        $review_post_ids = $wpdb->get_col(
            "SELECT DISTINCT post_id FROM {$wpdb->postmeta} WHERE meta_key = 'wpcr3_review_post'"
        );

        

          $imported = 0;
          $data = [];
        if ($review_post_ids) {
            foreach ($review_post_ids as $post_id) {
              
            // Get all relevant meta values for this review
            $postID    = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_post' LIMIT 1",
                $post_id
            ));
            $name    = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_name' LIMIT 1",
                $post_id
            ));
            $email   = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_email' LIMIT 1",
                $post_id
            ));
            $rating  = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_rating' LIMIT 1",
                $post_id
            ));
            $title   = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_title' LIMIT 1",
                $post_id
            ));
            $website = $wpdb->get_var($wpdb->prepare(
                "SELECT meta_value FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key = 'wpcr3_review_website' LIMIT 1",
                $post_id
            ));


       

        // Get post_date and post_content from wp_posts for this review
        $post_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT post_date, post_content FROM {$wpdb->posts} WHERE ID = %d LIMIT 1",
                $post_id
            )
        );

        

              

            
                $data = [
                    'name' => $name,
                    'email' => $email,
                    'phone' => '',
                    'website' => $website,
                    'city' => '',
                    'state' => '',
                    'title' => $title, 
                    'comment' => $post_data->post_content,
                    'rating' => $rating,
                    'status' => 'approved',
                    'positionid' => $postID,
                    'created_at' => $post_data->post_date
                ];

               

                $this->ctrw_add_review($data);
                    $imported++;
                
            
        }
    }

        return $imported;
    }

    public function delete_reviews($review_ids) {
        $this->wpdb->query(
            "DELETE FROM $this->table WHERE id IN (" . implode(',', array_map('intval', $review_ids)) . ")"
        );
    }

    public function ctrw_add_review($data) {
        return $this->wpdb->insert($this->table, $data);
    }

    public function get_reviews($status = 'approved'){
        return $this->wpdb->get_results("SELECT * FROM {$this->table} WHERE status = '$status' ORDER BY created_at DESC");
    }
    public function get_review_by_id($id) {
        return $this->wpdb->get_results("SELECT * FROM {$this->table} WHERE positionid = $id ORDER BY created_at DESC");
        return $this->wpdb->get_row($this->wpdb->prepare("SELECT * FROM $this->table WHERE id = %d", $id));
    }
    public function get_review_count_by_positionid($positionid) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM $this->table WHERE positionid = %d", $positionid)
        );
        return (int) $count;
    }
    public function get_average_rating_by_positionid($positionid) {
        $avg = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT AVG(rating) FROM $this->table WHERE positionid = %d AND status = %s",
                $positionid,
                'approved'
            )
        );
        return $avg !== null ? round((float)$avg, 2) : 0;
    }


    public function update_review($id, $data) {
     
      return $this->wpdb->update($this->table, $data, ['id' => $id]);
    }

    public function delete_review($id) {
        return $this->wpdb->delete($this->table, ['id' => $id]);
    }

    public function edit_customer_review($post) {
        $data = [
            'name' => sanitize_text_field($post['name']),
            'email' => sanitize_email($post['email']),
            'website' => esc_url($post['website']),
            'phone' => sanitize_text_field($post['phone']),
            'city' => sanitize_text_field($post['city']),
            'state' => sanitize_text_field($post['state']),
            'title' => sanitize_text_field($post['title']),
            'comment' => sanitize_textarea_field($post['comment']),
            'rating' => intval($post['rating']),
            'status' => sanitize_text_field($post['status']),
        ];
        $id = intval($post['id']);

        if ($this->wpdb->update($this->table, $data, ['id' => $id])) {
            return true;
        } else {
            return false;
        }
    }

    public function check_replace_woocommerce_reviews() {
        $setting = get_option('customer_reviews_settings');
        if (!empty($setting['replace_woocommerce_reviews'])) {
            return true; // Return early if the setting is not enabled
        }
    }

    public function get_review_count_by_status($status) {
        $count = $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM $this->table WHERE status = %s", $status)
        );
        return (int) $count;
    }
}
?>
