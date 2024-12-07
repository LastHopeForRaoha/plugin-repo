<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MKWARewardsStore {
    private static $rewards_table;
    private static $rewards_log_table;

    public static function init() {
        global $wpdb;
        self::$rewards_table = $wpdb->prefix . 'mkwa_rewards';
        self::$rewards_log_table = $wpdb->prefix . 'mkwa_rewards_log';

        // Hook to create tables
        add_action('plugins_loaded', [__CLASS__, 'create_tables']);
        // AJAX handler for redeeming rewards
        add_action('wp_ajax_mkwa_redeem_reward', [__CLASS__, 'redeem_reward']);
    }

    /**
     * Create the rewards and rewards log tables.
     */
    public static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        // Create rewards table
        $rewards_sql = "CREATE TABLE IF NOT EXISTS " . self::$rewards_table . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            reward_name VARCHAR(255) NOT NULL,
            description TEXT,
            points_required INT NOT NULL,
            stock INT DEFAULT 0,
            category ENUM('low', 'mid', 'high') NOT NULL,
            seasonal TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        // Create rewards log table
        $log_sql = "CREATE TABLE IF NOT EXISTS " . self::$rewards_log_table . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            reward_id BIGINT(20) UNSIGNED NOT NULL,
            redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($rewards_sql);
        dbDelta($log_sql);
    }

    /**
     * Display the Rewards Store for logged-in users.
     */
    public static function display_rewards_store() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access the Rewards Store.</p>';
        }

        global $wpdb;
        $user_id = get_current_user_id();
        $user_points = MKWAPointsSystem::get_user_points($user_id);
        $rewards = $wpdb->get_results("SELECT * FROM " . self::$rewards_table . " WHERE stock > 0", ARRAY_A);

        if (empty($rewards)) {
            return '<p>The Rewards Store is currently empty. Please check back later!</p>';
        }

        ob_start(); ?>
        <div class="mkwa-rewards-store">
            <h2>Rewards Store</h2>
            <p>You have <strong><?php echo esc_html($user_points); ?> points</strong>.</p>
            <ul>
                <?php foreach ($rewards as $reward): ?>
                    <li>
                        <strong><?php echo esc_html($reward['reward_name']); ?></strong> -
                        <?php echo esc_html($reward['points_required']); ?> points
                        <button class="redeem-reward" data-reward-id="<?php echo esc_attr($reward['id']); ?>">Redeem</button>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Redeem a reward via AJAX.
     */
    public static function redeem_reward() {
        // Verify user is logged in
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to redeem rewards.');
        }

        // Validate nonce
        check_ajax_referer('mkwa_redeem_reward_nonce', 'security');

        // Sanitize and validate reward ID
        $reward_id = isset($_POST['reward_id']) ? intval($_POST['reward_id']) : 0;
        if (!$reward_id) {
            wp_send_json_error('Invalid reward selected.');
        }

        $user_id = get_current_user_id();

        global $wpdb;
        $reward = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM " . self::$rewards_table . " WHERE id = %d",
            $reward_id
        ), ARRAY_A);

        if (!$reward || $reward['stock'] <= 0) {
            wp_send_json_error('This reward is out of stock.');
        }

        $user_points = MKWAPointsSystem::get_user_points($user_id);
        if ($user_points < $reward['points_required']) {
            wp_send_json_error('You do not have enough points for this reward.');
        }

        // Deduct points and update stock
        MKWAPointsSystem::deduct_user_points($user_id, $reward['points_required']);
        $wpdb->update(
            self::$rewards_table,
            ['stock' => $reward['stock'] - 1],
            ['id' => $reward_id],
            ['%d'],
            ['%d']
        );

        // Log redemption
        $wpdb->insert(self::$rewards_log_table, [
            'user_id' => $user_id,
            'reward_id' => $reward_id,
        ], ['%d', '%d']);

        wp_send_json_success('Reward redeemed successfully!');
    }
}

// Initialize the class
MKWARewardsStore::init();
