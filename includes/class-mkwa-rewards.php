<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWARewardsStore {
    private static $table_name; // Table for managing rewards

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_rewards';
        
        // Hook for handling AJAX requests for redemptions
        add_action('wp_ajax_mkwa_redeem_reward', [__CLASS__, 'redeem_reward']);
    }

    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reward_name VARCHAR(255) NOT NULL,
            points_required INT NOT NULL,
            stock INT DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function add_reward($reward_name, $points_required, $stock) {
        global $wpdb;
        $wpdb->insert(self::$table_name, [
            'reward_name' => $reward_name,
            'points_required' => $points_required,
            'stock' => $stock,
        ], ['%s', '%d', '%d']);
    }

    public static function get_rewards() {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM " . self::$table_name . " WHERE stock > 0");
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
        self::log_redemption($user_id, $reward->reward_name);

        wp_send_json_success('Reward redeemed successfully!');
    }

    public static function log_redemption($user_id, $reward_name) {
        // Log the redemption (could be stored in a database or sent as a notification)
        $message = sprintf('User %d redeemed reward: %s', $user_id, $reward_name);
        error_log($message); // For development/debugging purposes
    }

    public static function display_rewards_store($atts) {
        $user_id = get_current_user_id();
        $points = MKWAPointsSystem::get_user_points($user_id);
        $rewards = self::get_rewards();

        ob_start();
        echo '<div class="mkwa-rewards-store">';
        echo '<h2>Rewards Store</h2>';
        echo '<p>You have ' . esc_html($points) . ' points to spend.</p>';
        echo '<ul>';
        foreach ($rewards as $reward) {
            echo '<li>';
            echo esc_html($reward->reward_name) . ' - ' . esc_html($reward->points_required) . ' points';
            if ($reward->stock > 0) {
                echo ' <button class="redeem-reward" data-reward-id="' . esc_attr($reward->id) . '">Redeem</button>';
            } else {
                echo ' <span class="out-of-stock">Out of Stock</span>';
            }
            echo '</li>';
        }
        echo '</ul>';
        echo '</div>';
        return ob_get_clean();
    }
}
