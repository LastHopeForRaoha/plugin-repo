<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWABadgesSystem {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_badges';

        // Hook into points system activity to check badge eligibility.
        add_action('mkwa_points_activity', [__CLASS__, 'check_badge_awards'], 10, 2);

        // Add admin menu for badge management.
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            badge_slug VARCHAR(255) NOT NULL,
            awarded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_badge (user_id, badge_slug)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function check_badge_awards($user_id, $activity) {
        $badges = self::get_user_badges($user_id);

        $badge_definitions = [
            'checkin' => ['iron_bear', 100, "Iron Bear", "100 gym check-ins"],
            'attendance_streak' => ['7_day_warrior', 50, "7-Day Warrior", "7 consecutive days of attendance"],
            'referral' => ['social_bear', 200, "Social Bear", "Referred 5 members"],
            'seasonal_challenge' => ['seasonal_champion', 300, "Seasonal Champion", "Completed a seasonal challenge"],
            'class_attendance' => ['class_master', 150, "Class Master", "Attended 50 classes"],
            'lifetime_points' => ['point_legend', 500, "Point Legend", "Earned 5000 lifetime points"]
        ];

        if (isset($badge_definitions[$activity])) {
            [$badge_slug, $bonus_points, $badge_name, $description] = $badge_definitions[$activity];
            if (!in_array($badge_slug, $badges)) {
                self::assign_badge($user_id, $badge_slug, $bonus_points, $badge_name, $description);
            }
        }
    }

    private static function assign_badge($user_id, $badge_slug, $bonus_points, $badge_name, $description) {
        global $wpdb;

        $wpdb->insert(self::$table_name, [
            'user_id' => $user_id,
            'badge_slug' => $badge_slug,
        ], ['%d', '%s']);

        MKWAPointsSystem::add_points($user_id, $bonus_points, "Awarded badge: {$badge_name}");

        self::notify_user($user_id, $badge_name, $description);
    }

    public static function get_user_badges($user_id) {
        global $wpdb;
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT badge_slug FROM " . self::$table_name . " WHERE user_id = %d",
            $user_id
        ));
        return wp_list_pluck($results, 'badge_slug');
    }

    private static function notify_user($user_id, $badge_name, $description) {
        $user = get_userdata($user_id);
        $message = "Congratulations! You've earned the '{$badge_name}' badge: {$description}.";
        wp_mail($user->user_email, "New Badge Earned!", $message);
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            'Badge Management',
            'Manage Badges',
            'manage_options',
            'mkwa-badges',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function render_admin_page() {
        echo '<h1>Manage Badges</h1>';
        // Add badge management UI (future improvements).
    }
}

MKWABadgesSystem::init();
