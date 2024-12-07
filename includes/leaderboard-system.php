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
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        points INT NOT NULL,
        rank INT NOT NULL DEFAULT 0,
        badges TEXT,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE (user_id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Reset leaderboard action
function mkwa_reset_leaderboard() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized user');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    $wpdb->query("TRUNCATE TABLE $table_name");
    wp_redirect(admin_url('admin.php?page=mkwa_leaderboard')); // Replace with actual admin menu slug
    exit;
}

// Add admin menu for leaderboard management
add_action('admin_menu', function() {
    add_submenu_page(
        'mkwa-fitness',
        'Leaderboard Management',
        'Leaderboard',
        'manage_options',
        'mkwa-leaderboard',
        'mkwa_manage_leaderboards'
    );
});
