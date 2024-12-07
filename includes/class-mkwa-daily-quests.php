<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWADailyQuests {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_daily_quests';

        // Register admin menu
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
        // Add daily quests for all users via cron
        add_action('mkwa_assign_daily_quests', [__CLASS__, 'assign_daily_quests_to_all']);
        // Schedule daily cron if not already scheduled
        if (!wp_next_scheduled('mkwa_assign_daily_quests')) {
            wp_schedule_event(time(), 'daily', 'mkwa_assign_daily_quests');
        }
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            quest_name VARCHAR(255) NOT NULL,
            points INT NOT NULL,
            progress INT DEFAULT 0,
            goal INT DEFAULT 1,
            completed TINYINT(1) DEFAULT 0,
            date_assigned DATE NOT NULL,
            UNIQUE KEY user_quest_date (user_id, quest_name, date_assigned)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            'Daily Quest Management',
            'Daily Quests',
            'manage_options',
            'mkwa-daily-quests',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function render_admin_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quest'])) {
            $name = sanitize_text_field($_POST['quest_name']);
            $points = intval($_POST['quest_points']);
            $goal = intval($_POST['quest_goal']);
            $type = sanitize_text_field($_POST['quest_type']);

            $quests = self::get_default_quests();
            $quests[] = [
                'name' => $name,
                'points' => $points,
                'goal' => $goal,
                'type' => $type
            ];
            update_option('mkwa_default_daily_quests', $quests);
            echo '<p>Quest added successfully!</p>';
        }

        $quests = self::get_default_quests();
        ?>
        <h1>Daily Quest Management</h1>
        <form method="POST">
            <label for="quest_name">Quest Name:</label>
            <input type="text" id="quest_name" name="quest_name" required>
            <br>
            <label for="quest_points">Points:</label>
            <input type="number" id="quest_points" name="quest_points" required>
            <br>
            <label for="quest_goal">Goal (e.g., 3 sets):</label>
            <input type="number" id="quest_goal" name="quest_goal" required>
            <br>
            <label for="quest_type">Type:</label>
            <select id="quest_type" name="quest_type">
                <option value="individual">Individual</option>
                <option value="themed">Themed</option>
                <option value="group">Group</option>
            </select>
            <br><br>
            <button type="submit" name="add_quest">Add Quest</button>
        </form>
        <h2>Default Quests</h2>
        <ul>
            <?php foreach ($quests as $quest) : ?>
                <li>
                    <?php echo esc_html($quest['name']); ?> - <?php echo esc_html($quest['points']); ?> Points (Goal: <?php echo esc_html($quest['goal']); ?>, Type: <?php echo esc_html($quest['type']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    public static function get_default_quests() {
        return get_option('mkwa_default_daily_quests', [
            ['name' => 'Early Bird', 'points' => 5, 'goal' => 1, 'type' => 'individual'],
            ['name' => 'Cardio Starter', 'points' => 3, 'goal' => 1, 'type' => 'individual'],
            ['name' => 'Circuit Champion', 'points' => 8, 'goal' => 1, 'type' => 'group'],
        ]);
    }

    public static function assign_daily_quests_to_all() {
        $users = get_users(['fields' => ['ID']]);
        foreach ($users as $user) {
            self::assign_daily_quests($user->ID);
        }
    }

    public static function assign_daily_quests($user_id) {
        global $wpdb;
        $quests = self::get_default_quests();
        $date = current_time('Y-m-d');

        foreach ($quests as $quest) {
            $wpdb->insert(self::$table_name, [
                'user_id' => $user_id,
                'quest_name' => $quest['name'],
                'points' => $quest['points'],
                'progress' => 0,
                'goal' => $quest['goal'],
                'completed' => 0,
                'date_assigned' => $date
            ], ['%d', '%s', '%d', '%d', '%d', '%d', '%s']);
        }
    }

    public static function get_user_quests($user_id) {
        global $wpdb;
        $date = current_time('Y-m-d');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id = %d AND date_assigned = %s",
            $user_id, $date
        ));
    }

    public static function update_quest_progress($quest_id, $user_id, $increment = 1) {
        global $wpdb;

        $quest = $wpdb->get_row($wpdb->prepare(
            "SELECT progress, goal FROM " . self::$table_name . " WHERE id = %d AND user_id = %d",
            $quest_id, $user_id
        ));

        if ($quest) {
            $new_progress = min($quest->progress + $increment, $quest->goal);
            $completed = ($new_progress >= $quest->goal) ? 1 : 0;

            $wpdb->update(
                self::$table_name,
                ['progress' => $new_progress, 'completed' => $completed],
                ['id' => $quest_id, 'user_id' => $user_id],
                ['%d', '%d'],
                ['%d', '%d']
            );

            if ($completed) {
                MKWAPointsSystem::add_points($user_id, $quest->points, "Completed quest: {$quest->quest_name}");
            }
        }
    }
}
MKWADailyQuests::init();
