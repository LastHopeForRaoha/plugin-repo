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

    /**
     * Create the points table.
     */
    public static function create_table() {
        global $wpdb;
        self::init(); // Ensure table name is initialized
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT NOT NULL,
            description TEXT NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            streak_days INT DEFAULT 0,
            last_activity_date DATE,
            UNIQUE KEY user_last_activity (user_id, last_activity_date),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        if ($wpdb->last_error) {
            error_log('MKWAPointsSystem: Failed to create table. Error: ' . $wpdb->last_error);
        }
    }

    /**
     * Ensure the table exists before performing any operations.
     */
    private static function ensure_table_exists() {
        global $wpdb;
        if (!$wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'")) {
            self::create_table();
        }
    }

    /**
     * Calculate the ultimate score for a user.
     */
    public static function calculate_ultimate_score($user_id) {
        // Fetch metrics
        $attendance = self::get_attendance_score($user_id);
        $class_participation = self::get_class_participation_score($user_id);
        $streaks = self::get_streak_score($user_id);
        $points = self::get_user_points($user_id);
        $badges = MKWABadgesSystem::get_user_badge_count($user_id);
        $membership_duration = self::get_membership_duration_score($user_id);

        // Weighted Model
        $weighted_model = (
            0.2 * $attendance +
            0.15 * $class_participation +
            0.15 * $streaks +
            0.1 * $points +
            0.1 * $badges +
            0.3 * $membership_duration
        );

        // Statistical Adjustment (Placeholder)
        $statistical_adjustment = self::calculate_statistical_adjustment($user_id);

        // AI Predicted Impact (Placeholder)
        $ai_predicted_impact = self::calculate_ai_impact($user_id);

        // Combine all components
        $ultimate_score = 0.5 * $weighted_model + 0.3 * $statistical_adjustment + 0.2 * $ai_predicted_impact;

        return round($ultimate_score, 2); // Rounded for cleaner display
    }

    /**
     * Get attendance score for a user.
     */
    private static function get_attendance_score($user_id) {
        $attendance = get_user_meta($user_id, 'attendance_last_30_days', true) ?: 0;
        return min($attendance / 30, 1); // Normalized to 1
    }

    /**
     * Get class participation score for a user.
     */
    private static function get_class_participation_score($user_id) {
        $classes = get_user_meta($user_id, 'classes_attended_last_30_days', true) ?: 0;
        return min($classes / 10, 1); // Normalized to 1
    }

    /**
     * Get streak score for a user.
     */
    private static function get_streak_score($user_id) {
        $streak_days = get_user_meta($user_id, 'mkwa_streak_days', true) ?: 0;
        return min($streak_days / 30, 1); // Normalized to 1
    }

    /**
     * Get membership duration score.
     */
    private static function get_membership_duration_score($user_id) {
        $start_date = get_user_meta($user_id, 'membership_start_date', true);
        $days = (time() - strtotime($start_date)) / (60 * 60 * 24);
        return min($days / 365, 1); // Normalized to 1 (1 year = max score)
    }

    /**
     * Placeholder for statistical adjustment.
     */
    private static function calculate_statistical_adjustment($user_id) {
        return 0.8; // Static placeholder; integrate PCA or clustering here
    }

    /**
     * Placeholder for AI impact calculation.
     */
    private static function calculate_ai_impact($user_id) {
        return 0.9; // Static placeholder; integrate AI model here
    }

    /**
     * Add points for a user with an optional description.
     */
    public static function add_points($user_id, $points, $description = '') {
        global $wpdb;
        self::ensure_table_exists();

        $result = $wpdb->insert(
            self::$table_name,
            [
                'user_id' => $user_id,
                'points' => $points,
                'description' => $description,
            ],
            ['%d', '%d', '%s']
        );

        if ($result === false) {
            error_log('MKWAPointsSystem: Failed to add points. Error: ' . $wpdb->last_error);
        }

        // Update total points in user meta
        $total_points = self::get_user_points($user_id);
        update_user_meta($user_id, 'mkwa_total_points', $total_points);
    }

    /**
     * Deduct points for a user.
     */
    public static function deduct_user_points($user_id, $points) {
        self::add_points($user_id, -abs($points), 'Points deduction');
    }

    /**
     * Get total points for a user.
     */
    public static function get_user_points($user_id) {
        global $wpdb;
        self::ensure_table_exists();

        $points = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM " . self::$table_name . " WHERE user_id = %d",
            $user_id
        ));

        return $points ?: 0; // Return 0 if null
    }
}

MKWAPointsSystem::init();
