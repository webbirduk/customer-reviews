<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * CTRW_Model Class
 *
 * Handles all database interactions for the Customer Reviews plugin.
 * It is responsible for querying, inserting, updating, and deleting review data.
 */
class CTRW_Model {

    private wpdb $wpdb;
    private string $table;

    /**
     * Constructor.
     */
    public function __construct() {
        global $wpdb;
        $this->wpdb  = $wpdb;
        $this->table = $this->wpdb->prefix . 'customer_reviews';
    }

    /**
     * Retrieves reviews from the database based on their status.
     *
     * @param string $status The review status to filter by (e.g., 'approved', 'pending'). Use 'all' to get all reviews.
     * @return array An array of review objects.
     */
    public function ctrw_ctrw_get_reviews_by_status(string $status): array {
        if ('All' === $status) {
            return $this->wpdb->get_results("SELECT * FROM {$this->table} ORDER BY created_at DESC");
        }
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE status = %s ORDER BY created_at DESC",
                $status
            )
        );
    }

    /**
     * Gets the count of reviews for each status.
     *
     * @return array An associative array with status keys and count values.
     */
    public function ctrw_get_review_counts(): array {
        $counts = [
            'All'      => 0,
            'Approved' => 0,
            'Rejected' => 0,
            'Pending'  => 0,
            'Trash'    => 0,
        ];

        $results = $this->wpdb->get_results(
            "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status",
            ARRAY_A
        );

        $total = 0;
        if ($results) {
            foreach ($results as $row) {
                if (array_key_exists($row['status'], $counts)) {
                    $counts[$row['status']] = (int) $row['count'];
                    $total += (int) $row['count'];
                }
            }
        }
        $counts['All'] = $total;
        return $counts;
    }

    /**
     * Calculates the average rating for all approved reviews.
     *
     * @return float The average rating, rounded to one decimal place.
     */
    public function ctrw_get_average_rating(): float {
        $result = $this->wpdb->get_var(
            "SELECT AVG(rating) FROM {$this->table} WHERE status = 'approved'"
        );
        return $result ? round((float) $result, 1) : 0.0;
    }

    /**
     * Updates the status of one or more reviews.
     *
     * @param array $review_ids An array of review IDs to update.
     * @param string $status The new status to set.
     * @return int|false The number of rows updated, or false on error.
     */
    public function ctrw_ctrw_update_review_status(array $review_ids, string $status) {
        if (empty($review_ids)) {
            return false;
        }
        $ids_placeholder = implode(', ', array_fill(0, count($review_ids), '%d'));
        
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "UPDATE {$this->table} SET status = %s WHERE id IN ({$ids_placeholder})",
                array_merge([$status], $review_ids)
            )
        );
    }

    /**
     * Imports reviews from the "Site Reviews" plugin.
     *
     * @return int The number of reviews successfully imported.
     */
    public function ctrw_import_reviews_from_site_reviews_plugin(): int {
        $site_reviews = $this->wpdb->get_results(
            "SELECT ID, post_date, post_content, post_title
             FROM {$this->wpdb->posts} 
             WHERE post_type = 'site-review' AND post_status = 'publish'"
        );

        $imported_count = 0;
        if (empty($site_reviews)) {
            return 0;
        }

        foreach ($site_reviews as $review) {
            $glsr_rating = $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT name, rating, email FROM {$this->wpdb->prefix}glsr_ratings WHERE review_id = %d LIMIT 1",
                    $review->ID
                ),
                ARRAY_A
            );

            $review_data = [
                'name'       => $glsr_rating['name'] ?? 'Anonymous',
                'email'      => $glsr_rating['email'] ?? '',
                'rating'     => $glsr_rating['rating'] ?? 0,
                'title'      => $review->post_title,
                'comment'    => $review->post_content,
                'status'     => 'approved',
                'created_at' => $review->post_date
            ];
            
            if ($this->ctrw_add_review($review_data)) {
                $imported_count++;
            }
        }
        
        return $imported_count;
    }

    /**
     * Imports reviews from the "WP Customer Reviews" plugin.
     *
     * @return int The number of reviews successfully imported.
     */
    public function ctrw_import_reviews_from_wp_customer_reviews(): int {
        $review_posts = $this->wpdb->get_results(
            "SELECT p.ID, p.post_date, p.post_content 
            FROM {$this->wpdb->posts} p
            JOIN {$this->wpdb->postmeta} pm ON p.ID = pm.post_id
            WHERE pm.meta_key = 'wpcr3_review' AND pm.meta_value = '1'"
        );

        $imported_count = 0;
        if (empty($review_posts)) {
            return 0;
        }

        foreach ($review_posts as $post) {
            $meta = get_post_meta($post->ID);
            
            $review_data = [
                'name'       => $meta['wpcr3_review_name'][0] ?? 'Anonymous',
                'email'      => $meta['wpcr3_review_email'][0] ?? '',
                'website'    => $meta['wpcr3_review_website'][0] ?? '',
                'title'      => $meta['wpcr3_review_title'][0] ?? '',
                'rating'     => $meta['wpcr3_review_rating'][0] ?? 0,
                'positionid' => $meta['wpcr3_review_post'][0] ?? 0,
                'comment'    => $post->post_content,
                'status'     => 'approved',
                'created_at' => $post->post_date
            ];

            if ($this->ctrw_add_review($review_data)) {
                $imported_count++;
            }
        }

        return $imported_count;
    }

    /**
     * Deletes one or more reviews permanently.
     *
     * @param array $review_ids An array of review IDs to delete.
     * @return int|false The number of rows deleted, or false on error.
     */
    public function ctrw_delete_reviews(array $review_ids) {
        if (empty($review_ids)) {
            return false;
        }
        $ids_placeholder = implode(', ', array_fill(0, count($review_ids), '%d'));
        
        return $this->wpdb->query(
            $this->wpdb->prepare(
                "DELETE FROM {$this->table} WHERE id IN ({$ids_placeholder})",
                $review_ids
            )
        );
    }

    /**
     * Adds a new review to the database.
     *
     * @param array $data An associative array of review data.
     * @return int|false The number of rows inserted (1), or false on error.
     */
    public function ctrw_add_review(array $data) {
        return $this->wpdb->insert($this->table, $data);
    }

    /**
     * Retrieves all reviews of a given status, ordered by creation date.
     *
     * @param string $status The status of reviews to fetch (defaults to 'approved').
     * @return array An array of review objects.
     */
    public function ctrw_get_reviews(string $status = 'approved'): array {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table} WHERE status = %s ORDER BY created_at DESC",
                $status
            )
        );
    }

    /**
     * Retrieves a single review by its ID.
     *
     * @param int $id The ID of the review.
     * @return object|null The review object, or null if not found.
     */
    public function ctrw_get_review_by_id(int $id): ?object {
        return $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id)
        );
    }

    /**
     * Gets the total count of approved reviews for a specific post ID.
     *
     * @param int $positionid The post ID (positionid).
     * @return int The number of reviews.
     */
    public function ctrw_get_review_count_by_positionid(int $positionid): int {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->table} WHERE positionid = %d AND status = 'approved'",
                $positionid
            )
        );
    }

    /**
     * Calculates the average rating for a specific post ID.
     *
     * @param int $positionid The post ID (positionid).
     * @return float The average rating.
     */
    public function ctrw_get_average_rating_by_positionid(int $positionid): float {
        $avg = $this->wpdb->get_var(
            $this->wpdb->prepare(
                "SELECT AVG(rating) FROM {$this->table} WHERE positionid = %d AND status = 'approved'",
                $positionid
            )
        );
        return $avg !== null ? round((float)$avg, 2) : 0.0;
    }

    /**
     * Updates an existing review in the database.
     *
     * @param int $id The ID of the review to update.
     * @param array $data An associative array of data to update.
     * @return int|false The number of rows updated, or false on error.
     */
    public function ctrw_update_review(int $id, array $data) {
        return $this->wpdb->update($this->table, $data, ['id' => $id]);
    }

    /**
     * Gets the total count of reviews for a specific status.
     *
     * @param string $status The status to count reviews for.
     * @return int The number of reviews with the given status.
     */
    public function ctrw_get_review_count_by_status(string $status): int {
        return (int) $this->wpdb->get_var(
            $this->wpdb->prepare("SELECT COUNT(*) FROM {$this->table} WHERE status = %s", $status)
        );
    }
}