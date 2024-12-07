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
    if (class_exists('MKWARegistration')) MKWARegistration::create_table(); // For user profiles
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
    'mkwa_badge_showcase' => function () {
        $user_id = get_current_user_id();
        ob_start();
        MKWADashboard::display_user_badges($user_id);
        return ob_get_clean();
    },
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
        function () {
            if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['award_badge'])) {
                $user_id = intval($_POST['user_id']);
                $badge_slug = sanitize_text_field($_POST['badge_slug']);
                if (class_exists('MKWABadgesSystem')) {
                    MKWABadgesSystem::assign_badge(
                        $user_id,
                        $badge_slug,
                        MKWABadgesSystem::get_user_badges($user_id),
                        0,
                        "Custom Badge",
                        "Manually awarded badge"
                    );
                    echo '<p>Badge awarded successfully to User ID ' . esc_html($user_id) . '.</p>';
                } else {
                    echo '<p>Error: Badge system is not available.</p>';
                }
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
