<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: Gamification features for MKWA Fitness, including points, badges, daily quests, workout buddy finder, leaderboard, dashboard, rewards store, and user registration/profile management.
 * Version: 2.1.5
 * Author: MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

// Load Text Domain (Translations) at the correct time
add_action('init', function () {
    load_plugin_textdomain('mkwafitness', false, dirname(plugin_basename(__FILE__)) . '/languages');
});

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
    'class-mkwa-activity-history.php',
    'helpers.php', // Common utility functions like mkwa_get_profile_data
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
    $classes = [
        'MKWAPointsSystem',
        'MKWABadgesSystem',
        'MKWADailyQuests',
        'MKWABuddyFinder',
        'MKWALeaderboard',
        'MKWADashboard',
        'MKWARewardsStore',
        'MKWARegistration',
        'MKWAActivityHistory'
    ];

    foreach ($classes as $class) {
        if (class_exists($class)) {
            if (method_exists($class, 'init')) {
                call_user_func([$class, 'init']);
            } else {
                error_log("MKWA Fitness Plugin: Missing init method in class - $class");
            }
        } else {
            error_log("MKWA Fitness Plugin: Missing class - $class");
        }
    }
});

// Activation hook to create database tables
register_activation_hook(__FILE__, function () {
    $classes = [
        'MKWAPointsSystem',
        'MKWABadgesSystem',
        'MKWADailyQuests',
        'MKWABuddyFinder',
        'MKWALeaderboard',
        'MKWARewardsStore',
    ];

    foreach ($classes as $class) {
        if (class_exists($class) && method_exists($class, 'create_table')) {
            call_user_func([$class, 'create_table']);
        } else {
            error_log("MKWA Fitness Plugin: Missing create_table method in class - $class");
        }
    }
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
add_action('init', function () {
    $shortcodes = [
        'mkwa_daily_quests' => 'MKWADailyQuests::display_daily_quests',
        'mkwa_buddy_finder' => 'MKWABuddyFinder::display_buddy_finder',
        'mkwa_leaderboard' => 'MKWALeaderboard::display_leaderboard',
        'mkwa_dashboard' => 'MKWADashboard::display_dashboard',
        'mkwa_rewards_store' => 'MKWARewardsStore::display_rewards_store',
        'mkwa_registration_form' => 'MKWARegistration::display_registration_form',
    ];

    foreach ($shortcodes as $tag => $callback) {
        if (is_callable($callback)) {
            add_shortcode($tag, $callback);
        } else {
            error_log("MKWA Fitness Plugin: Invalid shortcode callback for [$tag]. Ensure the method exists and is public.");
        }
    }
});

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

// Fallback for undefined functions
if (!function_exists('mkwa_get_profile_data')) {
    function mkwa_get_profile_data($user_id) {
        $user = get_userdata($user_id);
        return [
            'display_name' => $user->display_name ?? '',
            'bio' => get_user_meta($user_id, 'mkwa_bio', true),
            'points' => get_user_meta($user_id, 'mkwa_total_points', true),
        ];
    }
}
