<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: Gamification features for MKWA Fitness, including points, badges, daily quests, workout buddy finder, leaderboard, dashboard, and rewards store.
 * Version: 1.4
 * Author: MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files for all features
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-points-system.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-badges-system.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-daily-quests.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-buddy-finder.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-leaderboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-dashboard.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-rewards-store.php';

// Initialize all classes
add_action('plugins_loaded', function() {
    MKWAPointsSystem::init();
    MKWABadgesSystem::init();
    MKWADailyQuests::init();
    MKWABuddyFinder::init();
    MKWALeaderboard::init();
    MKWADashboard::init();
    MKWARewardsStore::init();
});

// Activation hook for database setup
register_activation_hook(__FILE__, function() {
    MKWAPointsSystem::create_table();
    MKWABadgesSystem::create_table();
    MKWADailyQuests::create_table();
    MKWABuddyFinder::create_table();
    MKWALeaderboard::create_table(); // Ensure leaderboard table creation if required
    MKWARewardsStore::create_table(); // Rewards Store table
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('mkwa-styles', plugin_dir_url(__FILE__) . 'assets/css/mkwa-styles.css');
    wp_enqueue_script('mkwa-scripts', plugin_dir_url(__FILE__) . 'assets/js/mkwa-scripts.js', ['jquery'], null, true);
    wp_localize_script('mkwa-scripts', 'mkwaAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

// Shortcodes for displaying features
add_shortcode('mkwa_daily_quests', function($atts) {
    return MKWADailyQuests::display_daily_quests($atts);
});

add_shortcode('mkwa_buddy_finder', function($atts) {
    return MKWABuddyFinder::display_buddy_finder($atts);
});

add_shortcode('mkwa_leaderboard', function($atts) {
    return MKWALeaderboard::display_leaderboard($atts);
});

add_shortcode('mkwa_dashboard', function($atts) {
    return MKWADashboard::display_dashboard($atts);
});

add_shortcode('mkwa_rewards_store', function($atts) {
    return MKWARewardsStore::display_rewards_store($atts);
});

// Custom admin page for plugin settings (if included in the old version)
add_action('admin_menu', function() {
    add_menu_page(
        'MKWA Fitness Plugin',
        'MKWA Fitness',
        'manage_options',
        'mkwa-fitness',
        function() {
            echo '<h1>MKWA Fitness Plugin Settings</h1>';
            echo '<p>Configure your MKWA Fitness Plugin settings here.</p>';
        }
    );
});

// Legacy compatibility code (if applicable from the old version)
// Retain any additional logic from the old file for backward compatibility
