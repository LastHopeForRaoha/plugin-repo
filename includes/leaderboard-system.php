<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create leaderboard table
function mkwa_create_leaderboard_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_leaderboard';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        user_id INT NOT NULL,
        points INT NOT NULL,
        rank INT NOT NULL,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>
