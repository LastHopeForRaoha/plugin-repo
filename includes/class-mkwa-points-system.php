<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWAPointsSystem {
    public static function init() {
        // Create the points table upon plugin activation
        register_activation_hook(__FILE__, [__CLASS__, 'create_table']);
    }

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mkwa_points';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            points INT NOT NULL,
            description TEXT NOT NULL,
            date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function add_points($user_id, $points, $description = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mkwa_points';

        $wpdb->insert($table_name, [
            'user_id' => $user_id,
            'points' => $points,
            'description' => $description,
        ]);

        // Update user meta with total points
        $total_points = self::get_user_points($user_id);
        update_user_meta($user_id, 'mkwa_total_points', $total_points);
    }

    public static function get_user_points($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mkwa_points';

        $points = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(points) FROM $table_name WHERE user_id = %d",
            $user_id
        ));

        return $points ? $points : 0;
    }

    public static function get_user_points_log($user_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'mkwa_points';

        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM $table_name WHERE user_id = %d ORDER BY date DESC",
            $user_id
        ));
    }
}
