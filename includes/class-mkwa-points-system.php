<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWAPointsSystem {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_points';

        // Add scheduled action for resetting streaks
        add_action('mkwa_reset_streaks_daily', [__CLASS__, 'reset_daily_streaks']);
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT NOT NULL,
            description TEXT NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            streak_days INT DEFAULT 0,
            last_activity_date DATE,
            PRIMARY KEY (id),
            UNIQUE KEY user_last_activity (user_id, last_activity_date)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Add points for a user with an optional description.
     */
    public static function add_points($user_id, $points, $description = '') {
        global $wpdb;

        $wpdb->insert(self::$table_name, [
            'user_id' => $user_id,
            'points' => $points,
            'description' => $description,
        ], ['%d', '%d', '%s']);

        // Update total points in user meta
        $total_points = self::get_user_points($user_id);
        update_user_meta($user_id, 'mkwa_total_points', $total_points);
    }

    /**
     * Deduct points for a user.
     */
    public static function deduct_user_points($user_id, $points) {
        global $wpdb;
        $wpdb->insert(self::$table_name, [
            'user_id' => $user_id,
            'points' => -abs($points),
            'description' => 'Points deduction',
        ], ['%d', '%d', '%s']);

        $total_points = self::get_user_points($user_id);
        update_user_meta($user_id, 'mkwa_total_points', $total_points);
    }

    /**
     * Get total points for a user.
     */
    public static function get_user_points($user_id) {
        global $wpdb;
        $points = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM " . self::$table_name . " WHERE user_id = %d",
            $user_id
        ));

        return $points ? $points : 0;
    }

    /**
     * Get a user's points log.
     */
    public static function get_user_points_log($user_id) {
        global $wpdb;
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id = %d ORDER BY date DESC",
            $user_id
        ));
    }

    /**
     * Track daily streaks.
     */
    public static function update_daily_streak($user_id) {
        global $wpdb;
        $last_activity = $wpdb->get_var($wpdb->prepare(
            "SELECT last_activity_date FROM " . self::$table_name . " WHERE user_id = %d ORDER BY last_activity_date DESC LIMIT 1",
            $user_id
        ));

        $today = current_time('Y-m-d');
        if ($last_activity === $today) {
            return; // Already updated today
        }

        $streak_days = 1;
        if ($last_activity && date('Y-m-d', strtotime($last_activity . ' +1 day')) === $today) {
            // Continue streak
            $streak_days = $wpdb->get_var($wpdb->prepare(
                "SELECT streak_days FROM " . self::$table_name . " WHERE user_id = %d ORDER BY last_activity_date DESC LIMIT 1",
                $user_id
            )) + 1;
        }

        $wpdb->insert(self::$table_name, [
            'user_id' => $user_id,
            'points' => 5, // Reward for maintaining streak
            'description' => 'Daily streak bonus',
            'streak_days' => $streak_days,
            'last_activity_date' => $today,
        ], ['%d', '%d', '%s', '%d', '%s']);

        // Update user meta for streak
        update_user_meta($user_id, 'mkwa_streak_days', $streak_days);
    }

    /**
     * Reset daily streaks for inactive users.
     */
    public static function reset_daily_streaks() {
        global $wpdb;
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $wpdb->query($wpdb->prepare(
            "UPDATE " . self::$table_name . " SET streak_days = 0 WHERE last_activity_date < %s",
            $yesterday
        ));
    }

    /**
     * Award bonus points for group class attendance.
     */
    public static function award_group_class_bonus($user_id, $class_id) {
        self::add_points($user_id, 10, 'Group class bonus for class ID: ' . $class_id);
    }

    /**
     * Award points for referrals.
     */
    public static function award_referral_bonus($referrer_id, $referred_id) {
        self::add_points($referrer_id, 50, 'Referral bonus for referring user ID: ' . $referred_id);
        self::add_points($referred_id, 25, 'Welcome bonus for joining via referral');
    }
}
