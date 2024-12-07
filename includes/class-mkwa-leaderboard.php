<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWALeaderboard {
    private static $table_name; // Points table name

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_points';

        // Register shortcode for leaderboard display
        add_shortcode('mkwa_leaderboard', [__CLASS__, 'display_leaderboard']);

        // Optional: Add cron job or manual trigger for leaderboard updates
        add_action('mkwa_update_leaderboard', [__CLASS__, 'update_leaderboard']);
    }

    /**
     * Display the leaderboard.
     */
    public static function display_leaderboard($atts) {
        $atts = shortcode_atts(['limit' => 10], $atts, 'mkwa_leaderboard');
        $leaderboard_data = self::get_top_users($atts['limit']);

        if (empty($leaderboard_data)) {
            return '<p>The leaderboard is currently empty. Check back later!</p>';
        }

        ob_start();
        ?>
        <div class="mkwa-leaderboard">
            <h2>Leaderboard</h2>
            <ol>
                <?php foreach ($leaderboard_data as $user) : ?>
                    <?php 
                    $user_info = get_userdata($user->user_id); 
                    if (get_user_meta($user->user_id, 'mkwa_leaderboard_optout', true)) {
                        continue; // Skip users who opted out.
                    }
                    ?>
                    <li>
                        <strong><?php echo esc_html($user_info->display_name); ?></strong> - 
                        <?php echo esc_html($user->points); ?> Points
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Fetch the top users for the leaderboard.
     */
    public static function get_top_users($limit = 10) {
        global $wpdb;

        // Ensure the points table exists
        if (!$wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'")) {
            return [];
        }

        // Query the top users
        return $wpdb->get_results($wpdb->prepare(
            "SELECT user_id, SUM(points) AS points 
             FROM " . self::$table_name . " 
             GROUP BY user_id 
             ORDER BY points DESC 
             LIMIT %d",
            $limit
        ));
    }

    /**
     * Update leaderboard data (manual or via cron).
     */
    public static function update_leaderboard() {
        $users = get_users(['fields' => ['ID']]);
        $leaderboard_data = [];

        foreach ($users as $user) {
            $points = MKWAPointsSystem::get_user_points($user->ID) ?: 0;
            $leaderboard_data[$user->ID] = $points;
        }

        // Save leaderboard data as an option (for manual updates)
        update_option('mkwa_fitness_leaderboard', $leaderboard_data);
    }

    /**
     * Fallback: Fetch leaderboard data from the saved option (if direct database query is unavailable).
     */
    public static function get_saved_leaderboard($limit = 10) {
        $leaderboard_data = get_option('mkwa_fitness_leaderboard', []);

        if (empty($l
