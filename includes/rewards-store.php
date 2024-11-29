<?php
// Create rewards table on plugin activation
function mkwa_create_rewards_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_rewards';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text NOT NULL,
        points_required int NOT NULL,
        stock int NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create rewards log table for tracking redemptions
function mkwa_create_rewards_log_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_rewards_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        reward_id mediumint(9) NOT NULL,
        date_redeemed datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Run table creation during plugin activation
register_activation_hook(__FILE__, function() {
    mkwa_create_rewards_table();
    mkwa_create_rewards_log_table();
});

// Fetch all available rewards
function mkwa_get_rewards() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_rewards';

    return $wpdb->get_results("SELECT * FROM $table_name WHERE stock > 0 ORDER BY points_required ASC");
}

// Redeem a reward
function mkwa_redeem_reward($user_id, $reward_id) {
    global $wpdb;
    $rewards_table = $wpdb->prefix . 'mkwa_rewards';
    $user_points = mkwa_get_points($user_id);

    // Fetch reward details
    $reward = $wpdb->get_row($wpdb->prepare("SELECT * FROM $rewards_table WHERE id = %d", $reward_id));

    if (!$reward || $reward->stock <= 0) {
        return ['success' => false, 'message' => 'Reward is not available.'];
    }

    if ($user_points < $reward->points_required) {
        return ['success' => false, 'message' => 'Insufficient points.'];
    }

    // Deduct points and reduce stock
    $new_points = $user_points - $reward->points_required;
    update_user_meta($user_id, 'mkwa_points', $new_points);

    $wpdb->update($rewards_table, ['stock' => $reward->stock - 1], ['id' => $reward_id]);

    // Log the redemption
    $log_table = $wpdb->prefix . 'mkwa_rewards_log';
    $wpdb->insert($log_table, [
        'user_id' => $user_id,
        'reward_id' => $reward_id,
        'date_redeemed' => current_time('mysql'),
    ]);

    return ['success' => true, 'message' => "Reward '{$reward->name}' redeemed successfully."];
}

// Shortcode for the rewards store
function mkwa_rewards_store_shortcode() {
    ob_start();
    include MKWA_PLUGIN_PATH . 'templates/rewards-store.php';
    return ob_get_clean();
}
add_shortcode('mkwa_rewards_store', 'mkwa_rewards_store_shortcode');
?>
