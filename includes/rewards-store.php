<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create rewards table
function mkwa_create_rewards_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_rewards';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        reward_name VARCHAR(255) NOT NULL,
        reward_points INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Create rewards log table
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
?>
