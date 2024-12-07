<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function mkwa_create_challenges_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'mkwa_challenges';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT NOT NULL AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL UNIQUE,
        description TEXT NOT NULL,
        points INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_setup_theme', 'mkwa_create_challenges_table');
?>
