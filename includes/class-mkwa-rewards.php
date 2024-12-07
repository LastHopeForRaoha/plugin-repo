<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWARewardsStore {
    private static $table_name;
    private static $log_table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_rewards';
        self::$log_table_name = $wpdb->prefix . 'mkwa_rewards_log';

        // Hook for handling AJAX requests for redemptions
        add_action('wp_ajax_mkwa_redeem_reward', [__CLASS__, 'redeem_reward']);
        // Add admin menu for reward management
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
    }

    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $rewards_table = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reward_name VARCHAR(255) NOT NULL,
            description TEXT,
            points_required INT NOT NULL,
            stock INT DEFAULT 0,
            category ENUM('low', 'mid', 'high') NOT NULL,
            seasonal TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        $log_table = "CREATE TABLE " . self::$log_table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            reward_id BIGINT(20) UNSIGNED NOT NULL,
            reward_name VARCHAR(255) NOT NULL,
            points_spent INT NOT NULL,
            redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (reward_id) REFERENCES " . self::$table_name . "(id) ON DELETE CASCADE
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($rewards_table);
        dbDelta($log_table);
    }

    public static function redeem_reward() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to redeem rewards.');
        }

        $user_id = get_current_user_id();
        $reward_id = intval($_POST['reward_id']);

        global $wpdb;
        $reward = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::$table_name . " WHERE id = %d", $reward_id));

        if (!$reward || $reward->stock <= 0) {
            wp_send_json_error('This reward is out of stock.');
        }

        $user_points = MKWAPointsSystem::get_user_points($user_id);

        if ($user_points < $reward->points_required) {
            wp_send_json_error('You do not have enough points for this reward.');
        }

        // Deduct points and update stock
        MKWAPointsSystem::deduct_user_points($user_id, $reward->points_required);
        $wpdb->update(self::$table_name, ['stock' => $reward->stock - 1], ['id' => $reward_id], ['%d'], ['%d']);

        // Log the redemption
        $wpdb->insert(self::$log_table_name, [
            'user_id' => $user_id,
            'reward_id' => $reward_id,
            'reward_name' => $reward->reward_name,
            'points_spent' => $reward->points_required,
        ], ['%d', '%d', '%s', '%d']);

        wp_send_json_success('Reward redeemed successfully!');
    }

    public static function display_redemption_history($user_id) {
        global $wpdb;
        $history = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM " . self::$log_table_name . " WHERE user_id = %d ORDER BY redeemed_at DESC",
            $user_id
        ));

        if (empty($history)) {
            echo '<p>No rewards redeemed yet.</p>';
            return;
        }

        echo '<h3>Your Redemption History</h3>';
        echo '<ul>';
        foreach ($history as $entry) {
            echo '<li>';
            echo esc_html($entry->reward_name) . ' - ';
            echo esc_html($entry->points_spent) . ' points ';
            echo '<small>(Redeemed on: ' . esc_html($entry->redeemed_at) . ')</small>';
            echo '</li>';
        }
        echo '</ul>';
    }
}
