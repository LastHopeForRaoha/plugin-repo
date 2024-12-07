<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: Gamification features for MKWA Fitness, including points, badges, daily quests, workout buddy finder, leaderboard, dashboard, rewards store, and user registration/profile management.
 * Version: 2.1
 * Author: MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files for all features
$includes = [
    'class-mkwa-points-system.php',
    'class-mkwa-badges-system.php',
    'class-mkwa-daily-quests.php',
    'class-mkwa-buddy-finder.php',
    'class-mkwa-leaderboard.php',
    'class-mkwa-dashboard.php',
    'class-mkwa-rewards-store.php',
    'class-mkwa-registration.php',
];

foreach ($includes as $file) {
    $path = plugin_dir_path(__FILE__) . 'includes/' . $file;
    if (file_exists($path)) {
        require_once $path;
    } else {
        error_log("MKWA Fitness Plugin: Missing required file - $file");
    }
}

// Initialize all classes
add_action('plugins_loaded', function () {
    if (class_exists('MKWAPointsSystem')) MKWAPointsSystem::init();
    if (class_exists('MKWABadgesSystem')) MKWABadgesSystem::init();
    if (class_exists('MKWADailyQuests')) MKWADailyQuests::init();
    if (class_exists('MKWABuddyFinder')) MKWABuddyFinder::init();
    if (class_exists('MKWALeaderboard')) MKWALeaderboard::init();
    if (class_exists('MKWADashboard')) MKWADashboard::init();
    if (class_exists('MKWARewardsStore')) MKWARewardsStore::init();
    if (class_exists('MKWARegistration')) MKWARegistration::init();
});

// Activation hook to create database tables
register_activation_hook(__FILE__, function () {
    if (class_exists('MKWAPointsSystem')) MKWAPointsSystem::create_table();
    if (class_exists('MKWABadgesSystem')) MKWABadgesSystem::create_table();
    if (class_exists('MKWADailyQuests')) MKWADailyQuests::create_table();
    if (class_exists('MKWABuddyFinder')) MKWABuddyFinder::create_table();
    if (class_exists('MKWALeaderboard')) MKWALeaderboard::create_table();
    if (class_exists('MKWARewardsStore')) MKWARewardsStore::create_tables(); // Ensures rewards and log tables are created
    // MKWARegistration does not require table creation
});

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('mkwa-styles', plugin_dir_url(__FILE__) . 'assets/css/mkwa-styles.css');
    wp_enqueue_script('mkwa-scripts', plugin_dir_url(__FILE__) . 'assets/js/mkwa-scripts.js', ['jquery'], null, true);
    wp_localize_script('mkwa-scripts', 'mkwaAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

// Shortcodes for displaying features
$shortcodes = [
    'mkwa_daily_quests' => 'MKWADailyQuests::display_daily_quests',
    'mkwa_buddy_finder' => 'MKWABuddyFinder::display_buddy_finder',
    'mkwa_leaderboard' => 'MKWALeaderboard::display_leaderboard',
    'mkwa_dashboard' => 'MKWADashboard::display_dashboard',
    'mkwa_rewards_store' => 'MKWARewardsStore::display_rewards_store',
    'mkwa_registration_form' => 'MKWARegistration::display_registration_form',
];

foreach ($shortcodes as $tag => $callback) {
    add_shortcode($tag, $callback);
}

// Admin Menu for Settings
add_action('admin_menu', function () {
    add_menu_page(
        'MKWA Fitness Plugin',
        'MKWA Fitness',
        'manage_options',
        'mkwa-fitness',
        function () {
            echo '<h1>MKWA Fitness Plugin Settings</h1>';
            echo '<p>Configure your MKWA Fitness Plugin settings here.</p>';
        }
    );

    add_submenu_page(
        'mkwa-fitness',
        'Badge Management',
        'Badges',
        'manage_options',
        'mkwa-badge-management',
        [MKWABadgesSystem::class, 'render_admin_page']
    );
});
