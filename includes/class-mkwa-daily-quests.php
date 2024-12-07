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

        // Register shortcode for displaying daily quests
        add_shortcode('mkwa_daily_quests', [__CLASS__, 'display_user_quests']);

        // Schedule daily cron if not already scheduled
        if (!wp_next_scheduled('mkwa_assign_daily_quests')) {
            wp_schedule_event(time(), 'daily', 'mkwa_assign_daily_quests');
        }
    }

    /**
     * Create the daily quests table.
     */
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
            UNIQUE KEY user_quest_date (user_id, quest_name, date_assigned),
            KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Add admin menu for quest management.
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            __('Daily Quest Management', 'mkwafitness'),
            __('Daily Quests', 'mkwafitness'),
            'manage_options',
            'mkwa-daily-quests',
            [__CLASS__, 'render_admin_page']
        );
    }

    /**
     * Render the admin page for managing daily quests.
     */
    public static function render_admin_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_quest'])) {
            check_admin_referer('mkwa_add_daily_quest', 'mkwa_daily_quest_nonce');

            $name = sanitize_text_field($_POST['quest_name']);
            $points = intval($_POST['quest_points']);
            $goal = intval($_POST['quest_goal']);
            $type = sanitize_text_field($_POST['quest_type']);

            $quests = self::get_default_quests();
            $quests[] = [
                'name' => $name,
                'points' => $points,
                'goal' => $goal,
                'type' => $type,
            ];
            update_option('mkwa_default_daily_quests', $quests);
            echo '<p>' . esc_html__('Quest added successfully!', 'mkwafitness') . '</p>';
        }

        $quests = self::get_default_quests();
        ?>
        <h1><?php esc_html_e('Daily Quest Management', 'mkwafitness'); ?></h1>
        <form method="POST">
            <?php wp_nonce_field('mkwa_add_daily_quest', 'mkwa_daily_quest_nonce'); ?>
            <label for="quest_name"><?php esc_html_e('Quest Name:', 'mkwafitness'); ?></label>
            <input type="text" id="quest_name" name="quest_name" required>
            <br>
            <label for="quest_points"><?php esc_html_e('Points:', 'mkwafitness'); ?></label>
            <input type="number" id="quest_points" name="quest_points" required>
            <br>
            <label for="quest_goal"><?php esc_html_e('Goal:', 'mkwafitness'); ?></label>
            <input type="number" id="quest_goal" name="quest_goal" required>
            <br>
            <label for="quest_type"><?php esc_html_e('Type:', 'mkwafitness'); ?></label>
            <select id="quest_type" name="quest_type">
                <option value="individual"><?php esc_html_e('Individual', 'mkwafitness'); ?></option>
                <option value="group"><?php esc_html_e('Group', 'mkwafitness'); ?></option>
                <option value="themed"><?php esc_html_e('Themed', 'mkwafitness'); ?></option>
            </select>
            <br><br>
            <button type="submit" name="add_quest"><?php esc_html_e('Add Quest', 'mkwafitness'); ?></button>
        </form>
        <h2><?php esc_html_e('Default Quests', 'mkwafitness'); ?></h2>
        <ul>
            <?php foreach ($quests as $quest) : ?>
                <li>
                    <?php echo esc_html($quest['name']); ?> - <?php echo esc_html($quest['points']); ?> 
                    <?php esc_html_e('Points (Goal:', 'mkwafitness'); ?> <?php echo esc_html($quest['goal']); ?>, 
                    <?php esc_html_e('Type:', 'mkwafitness'); ?> <?php echo esc_html($quest['type']); ?>)
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Get default daily quests.
     */
    public static function get_default_quests() {
        return get_option('mkwa_default_daily_quests', [
            ['name' => 'Early Bird', 'points' => 5, 'goal' => 1, 'type' => 'individual'],
            ['name' => 'Cardio Starter', 'points' => 3, 'goal' => 1, 'type' => 'individual'],
            ['name' => 'Circuit Champion', 'points' => 8, 'goal' => 1, 'type' => 'group'],
        ]);
    }

    /**
     * Assign daily quests to all users.
     */
    public static function assign_daily_quests_to_all() {
        $users = get_users(['fields' => ['ID']]);
        foreach ($users as $user) {
            self::assign_daily_quests($user->ID);
        }
    }

    /**
     * Assign daily quests to a specific user.
     */
    public static function assign_daily_quests($user_id) {
        global $wpdb;

        if (!$wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'")) {
            return; // Table doesn't exist
        }

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
                'date_assigned' => $date,
            ], ['%d', '%s', '%d', '%d', '%d', '%s']);
        }
    }

    /**
     * Display user quests.
     */
    public static function display_user_quests() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return '<p>' . esc_html__('Please log in to view your daily quests.', 'mkwafitness') . '</p>';
        }

        $quests = self::get_user_quests($user_id);
        if (empty($quests)) {
            return '<p>' . esc_html__('No quests assigned for today.', 'mkwafitness') . '</p>';
        }

        ob_start();
        ?>
        <div class="mkwa-daily-quests">
            <h2><?php esc_html_e('Your Daily Quests', 'mkwafitness'); ?></h2>
            <ul>
                <?php foreach ($quests as $quest) : ?>
                    <li>
                        <strong><?php echo esc_html($quest->quest_name); ?></strong>:
                        <?php echo esc_html($quest->progress . '/' . $quest->goal); ?> 
                        <?php esc_html_e('completed.', 'mkwafitness'); ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get quests for a user for the current day.
     */
    public static function get_user_quests($user_id) {
        global $wpdb;

        if (!$wpdb->get_var("SHOW TABLES LIKE '" . self::$table_name . "'")) {
            return []; // Table doesn't exist
        }

        $date = current_time('Y-m-d');
        return $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$table_name . " WHERE user_id = %d AND date_assigned = %s",
            $user_id, $date
        ));
    }
}

MKWADailyQuests::init();
