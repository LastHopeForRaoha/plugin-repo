<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create Rewards Table
function mkwa_create_rewards_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_rewards';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        reward_name VARCHAR(255) NOT NULL,
        description TEXT,
        points_required INT NOT NULL,
        stock INT DEFAULT 0,
        category ENUM('low', 'mid', 'high') NOT NULL,
        seasonal TINYINT(1) DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create Rewards Log Table
function mkwa_create_rewards_log_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_rewards_log';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        reward_id INT NOT NULL,
        redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Shortcode for Rewards Store
function mkwa_display_rewards_store() {
    global $wpdb;
    $user_id = get_current_user_id();

    if (!$user_id) {
        return '<p>Please log in to access the Rewards Store.</p>';
    }

    $table_name = $wpdb->prefix . 'mkwa_rewards';
    $user_points = MKWAPointsSystem::get_user_points($user_id);
    $rewards = $wpdb->get_results("SELECT * FROM $table_name WHERE stock > 0");

    ob_start();
    echo '<div class="mkwa-rewards-store">';
    echo '<h2>Rewards Store</h2>';
    echo "<p>You have <strong>{$user_points} points</strong> available.</p>";
    echo '<ul>';
    foreach ($rewards as $reward) {
        echo '<li>';
        echo esc_html($reward->reward_name) . " - " . esc_html($reward->points_required) . " points";
        echo $reward->stock > 0
            ? " <button class='redeem-reward' data-reward-id='" . esc_attr($reward->id) . "'>Redeem</button>"
            : " <span class='out-of-stock'>Out of Stock</span>";
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('mkwa_rewards_store', 'mkwa_display_rewards_store');
