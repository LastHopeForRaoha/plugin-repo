<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWABuddyFinder {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_buddy_finder';

        // Schedule cleanup for inactive buddy finder entries (optional)
        if (!wp_next_scheduled('mkwa_clean_buddy_finder')) {
            wp_schedule_event(time(), 'daily', 'mkwa_clean_buddy_finder');
        }

        add_action('mkwa_clean_buddy_finder', [__CLASS__, 'cleanup_inactive_entries']);
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            availability TEXT NOT NULL,
            fitness_goals TEXT NOT NULL,
            opt_in TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function update_preferences($user_id, $availability, $fitness_goals, $opt_in) {
        global $wpdb;

        $data = [
            'availability' => maybe_serialize($availability),
            'fitness_goals' => maybe_serialize($fitness_goals),
            'opt_in' => $opt_in ? 1 : 0,
        ];
        $existing = $wpdb->get_var($wpdb->prepare("SELECT id FROM " . self::$table_name . " WHERE user_id = %d", $user_id));
        if ($existing) {
            $wpdb->update(self::$table_name, $data, ['user_id' => $user_id], ['%s', '%s', '%d'], ['%d']);
        } else {
            $data['user_id'] = $user_id;
            $wpdb->insert(self::$table_name, $data, ['%d', '%s', '%s', '%d']);
        }

        // Award points for updating preferences
        MKWAPointsSystem::add_points($user_id, 10, 'Updated Buddy Finder Preferences');
    }

    public static function get_user_preferences($user_id) {
        global $wpdb;
        $result = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::$table_name . " WHERE user_id = %d", $user_id));
        if ($result) {
            $result->availability = maybe_unserialize($result->availability);
            $result->fitness_goals = maybe_unserialize($result->fitness_goals);
        }
        return $result;
    }

    public static function find_buddies($user_id) {
        global $wpdb;
        $user_prefs = self::get_user_preferences($user_id);
        if (!$user_prefs || !$user_prefs->opt_in) {
            return [];
        }

        $matches = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id != %d AND opt_in = 1",
            $user_id
        ));

        $buddies = [];
        foreach ($matches as $match) {
            $match->availability = maybe_unserialize($match->availability);
            $match->fitness_goals = maybe_unserialize($match->fitness_goals);

            if (self::has_common_availability($user_prefs->availability, $match->availability) &&
                self::has_common_goals($user_prefs->fitness_goals, $match->fitness_goals)) {
                $buddies[] = $match;
            }
        }

        // Award points for finding buddies
        if (!empty($buddies)) {
            MKWAPointsSystem::add_points($user_id, 20, 'Buddy Finder Matches Found');
        }

        return $buddies;
    }

    private static function has_common_availability($avail1, $avail2) {
        return !empty(array_intersect($avail1, $avail2));
    }

    private static function has_common_goals($goals1, $goals2) {
        return !empty(array_intersect($goals1, $goals2));
    }

    public static function cleanup_inactive_entries() {
        global $wpdb;
        $wpdb->query($wpdb->prepare(
            "DELETE FROM " . self::$table_name . " WHERE opt_in = 0 AND created_at < %s",
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ));
    }
}
