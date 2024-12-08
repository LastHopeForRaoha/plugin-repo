<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWALeaderboard {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_leaderboard';

        // Register shortcode for leaderboard display
        add_shortcode('mkwa_leaderboard', [__CLASS__, 'display_leaderboard']);

        // Add scheduled action for leaderboard resets
        add_action('mkwa_reset_leaderboard_monthly', [__CLASS__, 'reset_monthly_leaderboard']);

        // Hook for admin leaderboard management
        add_action('admin_post_mkwa_reset_leaderboard', [__CLASS__, 'reset_leaderboard']);
    }

    /**
     * Create the leaderboard table.
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT NOT NULL DEFAULT 0,
            `rank` INT NOT NULL DEFAULT 0, -- Escaped `rank` as it is a reserved keyword
            category ENUM('overall', 'monthly', 'weekly') DEFAULT 'overall',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_category (user_id, category),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        if ($wpdb->last_error) {
            error_log('MKWALeaderboard: Error creating table - ' . $wpdb->last_error);
        }
    }

    /**
     * Display the leaderboard.
     */
    public static function display_leaderboard($atts) {
        $atts = shortcode_atts([
            'limit' => 10,
            'category' => 'overall',
        ], $atts, 'mkwa_leaderboard');

        $leaderboard_data = self::get_top_users($atts['limit'], $atts['category']);

        if (empty($leaderboard_data)) {
            return '<p>The leaderboard is currently empty. Check back later!</p>';
        }

        ob_start();
        ?>
        <div class="mkwa-leaderboard">
            <h2><?php echo esc_html(ucfirst($atts['category'])); ?> Leaderboard</h2>
            <ol>
                <?php foreach ($leaderboard_data as $user) : ?>
                    <?php
                    $user_info = get_userdata($user->user_id);

                    if (!$user_info) {
                        error_log('MKWALeaderboard: Invalid user ID ' . $user->user_id);
                        continue; // Skip invalid users
                    }

                    $display_name = esc_html($user_info->display_name ?: 'Anonymous');
                    ?>
                    <li>
                        <strong><?php echo $display_name; ?></strong> - 
                        <?php echo esc_html($user->points); ?> Points
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get top users for the leaderboard.
     */
    public static function get_top_users($limit = 10, $category = 'overall') {
        global $wpdb;

        $query = "SELECT user_id, points 
                  FROM " . self::$table_name . " 
                  WHERE category = %s
                  ORDER BY points DESC
                  LIMIT %d";

        $results = $wpdb->get_results($wpdb->prepare($query, $category, $limit));

        if ($wpdb->last_error) {
            error_log('MKWALeaderboard: Error fetching leaderboard data - ' . $wpdb->last_error);
        }

        return $results;
    }

    /**
     * Update leaderboard data for a specific category.
     */
    public static function update_leaderboard($category = 'overall') {
        global $wpdb;
        $users = get_users(['fields' => ['ID']]);

        foreach ($users as $user_id) {
            $points = MKWAPointsSystem::get_user_points($user_id);

            if ($points > 0) {
                $wpdb->replace(
                    self::$table_name,
                    [
                        'user_id' => $user_id,
                        'points' => $points,
                        'category' => $category,
                    ],
                    ['%d', '%d', '%s']
                );

                if ($wpdb->last_error) {
                    error_log('MKWALeaderboard: Error updating leaderboard for user ' . $user_id . ' - ' . $wpdb->last_error);
                }
            }
        }
    }

public function render_leaderboard() {
    global $wpdb;
    $leaderboard_data = $wpdb->get_results("SELECT user_id, points FROM {$wpdb->prefix}mkwa_points ORDER BY points DESC LIMIT 10");

    ob_start();
    echo '<h2>Leaderboard</h2><ul>';
    foreach ($leaderboard_data as $entry) {
        echo '<li>' . get_user_meta($entry->user_id, 'nickname', true) . ': ' . $entry->points . '</li>';
    }
    echo '</ul>';
    return ob_get_clean();
}

    /**
     * Reset monthly leaderboard.
     */
    public static function reset_monthly_leaderboard() {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "DELETE FROM " . self::$table_name . " WHERE category = %s",
            'monthly'
        ));

        if ($wpdb->last_error) {
            error_log('MKWALeaderboard: Error resetting monthly leaderboard - ' . $wpdb->last_error);
        }
    }

    /**
     * Reset leaderboard manually from the admin panel.
     */
    public static function reset_leaderboard() {
        if (!current_user_can('manage_options')) {
            wp_die('Unauthorized user');
        }

        global $wpdb;

        $wpdb->query("TRUNCATE TABLE " . self::$table_name);

        if ($wpdb->last_error) {
            error_log('MKWALeaderboard: Error resetting leaderboard - ' . $wpdb->last_error);
        }

        wp_redirect(admin_url('admin.php?page=mkwa-leaderboard'));
        exit;
    }

    /**
     * Calculate leaderboard ranks for a given category.
     */
    public static function calculate_ranks($category = 'overall') {
        global $wpdb;

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, points 
             FROM " . self::$table_name . " 
             WHERE category = %s 
             ORDER BY points DESC",
            $category
        ));

        if ($wpdb->last_error) {
            error_log('MKWALeaderboard: Error calculating ranks - ' . $wpdb->last_error);
            return;
        }

        $rank = 1;
        foreach ($results as $row) {
            $wpdb->update(
                self::$table_name,
                ['rank' => $rank],
                ['user_id' => $row->user_id, 'category' => $category],
                ['%d'],
                ['%d', '%s']
            );

            if ($wpdb->last_error) {
                error_log('MKWALeaderboard: Error updating rank for user ' . $row->user_id . ' - ' . $wpdb->last_error);
            }

            $rank++;
        }
    }
}

MKWALeaderboard::init();
