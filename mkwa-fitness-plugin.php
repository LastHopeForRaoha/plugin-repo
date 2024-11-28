<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: A comprehensive plugin for MKWA Fitness, including gamification, member profiles, rewards, challenges, leaderboards, and more.
 * Version: 1.1
 * Author: MKWA Fitness Team
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define constants
define('MKWA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MKWA_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include necessary files
include_once MKWA_PLUGIN_PATH . 'includes/member-profiles.php';
include_once MKWA_PLUGIN_PATH . 'includes/rewards-store.php';
include_once MKWA_PLUGIN_PATH . 'includes/challenges-system.php';
include_once MKWA_PLUGIN_PATH . 'includes/leaderboard-system.php';
include_once MKWA_PLUGIN_PATH . 'includes/rewards-management.php';
include_once MKWA_PLUGIN_PATH . 'includes/leaderboard-management.php';
include_once MKWA_PLUGIN_PATH . 'includes/admin-menu.php'; // Admin menu integration

// Enqueue plugin styles and scripts
function mkwa_enqueue_styles() {
    // Frontend styles
    wp_enqueue_style('mkwa-styles', MKWA_PLUGIN_URL . 'assets/css/styles.css');

    // Admin styles (only for MKWA admin pages)
    if (is_admin()) {
        $screen = get_current_screen();
        if (strpos($screen->id, 'mkwa') !== false) {
            wp_enqueue_style('mkwa-admin-styles', MKWA_PLUGIN_URL . 'assets/css/admin-styles.css');
        }
    }
}
add_action('wp_enqueue_scripts', 'mkwa_enqueue_styles');
add_action('admin_enqueue_scripts', 'mkwa_enqueue_styles');

// Create plugin tables on activation
function mkwa_activate_plugin() {
    // Include files to ensure table creation logic runs
    include_once MKWA_PLUGIN_PATH . 'includes/rewards-store.php';
    include_once MKWA_PLUGIN_PATH . 'includes/challenges-system.php';
    include_once MKWA_PLUGIN_PATH . 'includes/leaderboard-system.php';

    // Run activation hooks
    mkwa_create_rewards_table();
    mkwa_create_rewards_log_table();
    mkwa_create_leaderboard_table();
    mkwa_create_challenges_table();
}
register_activation_hook(__FILE__, 'mkwa_activate_plugin');

// Register shortcodes
function mkwa_register_shortcodes() {
    // Member dashboard
    add_shortcode('mkwa_dashboard', 'mkwa_member_dashboard_shortcode');
    // Rewards store
    add_shortcode('mkwa_rewards_store', 'mkwa_rewards_store_shortcode');
    // Challenges
    add_shortcode('mkwa_challenges', 'mkwa_active_challenges_shortcode');
    // Leaderboard
    add_shortcode('mkwa_leaderboard', 'mkwa_leaderboard_shortcode');
}
add_action('init', 'mkwa_register_shortcodes');

// Cleanup tasks on plugin deactivation
function mkwa_deactivate_plugin() {
    // Optionally clear temporary data, scheduled tasks, or cached data
}
register_deactivation_hook(__FILE__, 'mkwa_deactivate_plugin');

// Cleanup on uninstallation
function mkwa_uninstall_plugin() {
    global $wpdb;

    // Drop custom tables if necessary
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mkwa_rewards");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mkwa_rewards_log");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mkwa_challenges");
    $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}mkwa_leaderboard");
}
register_uninstall_hook(__FILE__, 'mkwa_uninstall_plugin');
?>
