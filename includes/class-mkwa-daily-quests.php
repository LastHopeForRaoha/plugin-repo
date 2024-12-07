<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWADailyQuests {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_daily_quests';
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            quest_name VARCHAR(255) NOT NULL,
            points INT NOT NULL,
            completed TINYINT(1) DEFAULT 0,
            date_assigned DATE NOT NULL,
            UNIQUE KEY user_quest_date (user_id, quest_name, date_assigned)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function assign_daily_quests($user_id) {
        global $wpdb;
        $quests = [
            ['name' => 'Early Bird', 'points' => 5],
            ['name' => 'Cardio Starter', 'points' => 3],
            ['name' => 'Circuit Champion', 'points' => 8],
        ];
        $date = current_time('Y-m-d');
        foreach ($quests as $quest) {
            $wpdb->insert(self::$table_name, [
                'user_id' => $user_id,
                'quest_name' => $quest['name'],
                'points' => $quest['points'],
                'completed' => 0,
                'date_assigned' => $date
            ], ['%d', '%s', '%d', '%d', '%s']);
        }
    }

    public static function get_user_quests($user_id) {
        global $wpdb;
        $date = current_time('Y-m-d');
        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id = %d AND date_assigned = %s",
            $user_id, $date
        ));
        return $results;
    }

    public static function mark_quest_completed($quest_id, $user_id) {
        global $wpdb;
        $wpdb->update(
            self::$table_name,
            ['completed' => 1],
            ['id' => $quest_id, 'user_id' => $user_id],
            ['%d'],
            ['%d', '%d']
        );
    }
}
