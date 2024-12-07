<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MKWAActivityHistory {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_activity_history';

        // Register database table creation
        register_activation_hook(__FILE__, [__CLASS__, 'create_table']);
    }

    /**
     * Create the activity history table.
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            activity_type VARCHAR(50) NOT NULL,
            description TEXT,
            points_earned INT DEFAULT 0,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Log user activity.
     */
    public static function log_activity($user_id, $activity_type, $description = '', $points_earned = 0) {
        global $wpdb;

        $wpdb->insert(self::$table_name, [
            'user_id' => $user_id,
            'activity_type' => $activity_type,
            'description' => $description,
            'points_earned' => $points_earned,
        ], ['%d', '%s', '%s', '%d']);
    }

    /**
     * Retrieve activity history for a user.
     */
    public static function get_user_history($user_id) {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id = %d ORDER BY date DESC",
            $user_id
        ), ARRAY_A);
    }
}

// Initialize the class
MKWAActivityHistory::init();
