<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Create challenges table
function mkwa_create_challenges_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_challenges';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        challenge_name VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        points INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
?>
