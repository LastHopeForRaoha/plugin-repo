<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: Gamification features for MKWA Fitness, including points, badges, and daily quests.
 * Version: 1.0
 * Author: MKWA Fitness
 */

if (!defined('ABSPATH')) {
    exit;
}

// Include necessary files for new and existing features
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-points-system.php'; // Existing points system
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-badges-system.php'; // Existing badges system
require_once plugin_dir_path(__FILE__) . 'includes/class-mkwa-daily-quests.php'; // New daily quests system

// Initialize classes for existing and new features
add_action('plugins_loaded', function() {
    MKWAPointsSystem::init(); // Existing points system
    MKWABadgesSystem::init(); // Existing badges system
    MKWADailyQuests::init();  // New daily quests system
});

// Activation hook for setting up required database tables
register_activation_hook(__FILE__, function() {
    MKWAPointsSystem::create_table(); // Ensure existing points table is created
    MKWABadgesSystem::create_table(); // Ensure existing badges table is created
    MKWADailyQuests::create_table();  // New: Daily quests table
});

// Enqueue scripts and styles for frontend
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('mkwa-styles', plugin_dir_url(__FILE__) . 'assets/css/mkwa-styles.css');
    wp_enqueue_script('mkwa-scripts', plugin_dir_url(__FILE__) . 'assets/js/mkwa-scripts.js', ['jquery'], null, true);
    wp_localize_script('mkwa-scripts', 'mkwaAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
});

// Shortcodes for frontend displays
add_shortcode('mkwa_daily_quests', function() {
    if (!is_user_logged_in()) {
        return '<p>Please log in to view your daily quests.</p>';
    }
    $user_id = get_current_user_id();
    $quests = MKWADailyQuests::get_user_quests($user_id);
    ob_start();
    echo '<ul class="mkwa-daily-quests">';
    foreach ($quests as $quest) {
        echo '<li data-quest-id="' . esc_attr($quest->id) . '">';
        echo esc_html($quest->quest_name) . ' - ' . esc_html($quest->points) . ' points';
        if ($quest->completed) {
            echo ' <span class="completed">Completed</span>';
        } else {
            echo ' <button class="complete-quest">Mark as Completed</button>';
        }
        echo '</li>';
    }
    echo '</ul>';
    return ob_get_clean();
});

// AJAX handler for completing quests
add_action('wp_ajax_mkwa_complete_quest', function() {
    if (!is_user_logged_in()) {
        wp_send_json_error('You must be logged in to complete quests.');
    }
    $quest_id = isset($_POST['quest_id']) ? intval($_POST['quest_id']) : 0;
    if ($quest_id > 0) {
        $user_id = get_current_user_id();
        MKWADailyQuests::mark_quest_completed($quest_id, $user_id);
        wp_send_json_success('Quest marked as completed.');
    } else {
        wp_send_json_error('Invalid quest ID.');
    }
});
?>
