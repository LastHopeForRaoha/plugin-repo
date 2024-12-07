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
            points INT NOT NULL,
            rank INT NOT NULL DEFAULT 0,
            category ENUM('overall', 'monthly', 'weekly') DEFAULT 'overall',
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY user_category (user_id, category)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
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
                        continue; // Skip users who no longer exist
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

        $query = "SELECT user_id, SUM(points) AS points
                  FROM " . self::$table_name . "
                  WHERE category = %s
                  GROUP BY user_id
                  ORDER BY points DESC
                  LIMIT %d";

        return $wpdb->get_results($wpdb->prepare($query, $category, $limit));
    }

    /**
     * Update leaderboard data (e.g., for periodic updates).
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
            }
        }
    }

    /**
     * Reset monthly leaderboard.
     */
    public static function reset_monthly_leaderboard() {
        global $wpdb;

        // Reset points for the 'monthly' category
        $wpdb->query($wpdb->prepare(
            "DELETE FROM " . self::$table_name . " WHERE category = %s",
            'monthly'
        ));
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
        wp_redirect(admin_url('admin.php?page=mkwa-leaderboard'));
        exit;
    }
}
