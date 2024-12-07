<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: Gamification features for MKWA Fitness, including points, badges, daily quests, workout buddy finder, leaderboard, dashboard, rewards store, and user registration/profile management.
 * Version: 2.0
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
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-registration.php';

// Initialize all classes
add_action('plugins_loaded', function() {
    MKWAPointsSystem::init();
    MKWABadgesSystem::init();
    MKWADailyQuests::init();
    MKWABuddyFinder::init();
    MKWALeaderboard::init();
    MKWADashboard::init();
    MKWARewardsStore::init();
    MKWARegistration::init();
});

// Activation hook to create database tables
register_activation_hook(__FILE__, function() {
    MKWAPointsSystem::create_table();
    MKWABadgesSystem::create_table();
    MKWADailyQuests::create_table();
    MKWABuddyFinder::create_table();
    MKWALeaderboard::create_table();
    MKWARewardsStore::create_table();
    MKWARegistration::create_table(); // For user profiles
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

add_shortcode('mkwa_registration_form', function($atts) {
    return MKWARegistration::display_registration_form($atts);
});

// Add badge showcase shortcode
add_shortcode('mkwa_badge_showcase', function($atts) {
    $user_id = get_current_user_id();
    ob_start();
    MKWADashboard::display_user_badges($user_id);
    return ob_get_clean();
});

// Admin Menu for Settings
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

    add_submenu_page(
        'mkwa-fitness',
        'Badge Management',
        'Badges',
        'manage_options',
        'mkwa-badge-management',
        function() {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['award_badge'])) {
                $user_id = intval($_POST['user_id']);
                $badge_slug = sanitize_text_field($_POST['badge_slug']);
                MKWABadgesSystem::assign_badge(
                    $user_id,
                    $badge_slug,
                    MKWABadgesSystem::get_user_badges($user_id),
                    0,
                    "Custom Badge",
                    "Manually awarded badge"
                );
                echo '<p>Badge awarded successfully to User ID ' . esc_html($user_id) . '.</p>';
            }
            ?>
            <h1>Badge Management</h1>
            <form method="POST">
                <label for="user_id">User ID:</label>
                <input type="number" id="user_id" name="user_id" required>
                <br><br>
                <label for="badge_slug">Badge Slug:</label>
                <input type="text" id="badge_slug" name="badge_slug" required>
                <br><br>
                <button type="submit" name="award_badge">Award Badge</button>
            </form>
            <?php
        }
    );
});
