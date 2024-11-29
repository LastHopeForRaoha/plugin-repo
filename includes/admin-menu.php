<?php
// Add MKWA Fitness menu to WordPress admin
function mkwa_admin_menu() {
    // Main Menu Page
    add_menu_page(
        'MKWA Fitness',              // Page title
        'MKWA Fitness',              // Menu title
        'manage_options',            // Capability
        'mkwa-fitness',              // Menu slug
        'mkwa_admin_dashboard',      // Callback function
        'dashicons-awards',          // Icon
        25                           // Position
    );

    // Add Submenu Pages
    add_submenu_page(
        'mkwa-fitness',              // Parent slug
        'Manage Challenges',         // Page title
        'Challenges',                // Menu title
        'manage_options',            // Capability
        'mkwa-challenges',           // Menu slug
        'mkwa_manage_challenges'     // Callback function
    );

    add_submenu_page(
        'mkwa-fitness',
        'Manage Rewards',
        'Rewards',
        'manage_options',
        'mkwa-rewards',
        'mkwa_manage_rewards'
    );

    add_submenu_page(
        'mkwa-fitness',
        'Leaderboards',
        'Leaderboards',
        'manage_options',
        'mkwa-leaderboards',
        'mkwa_manage_leaderboards'
    );
}

// Hook the function into the admin menu
add_action('admin_menu', 'mkwa_admin_menu');

// Admin Dashboard Page Callback
function mkwa_admin_dashboard() {
    echo '<div class="wrap"><h1>MKWA Fitness Admin Dashboard</h1>';
    echo '<p>Welcome to the MKWA Fitness plugin management system.</p>';
    echo '<p>Use the menu on the left to manage challenges, rewards, and leaderboards.</p></div>';
}

// Challenges Management Page Callback
function mkwa_manage_challenges() {
    echo '<div class="wrap"><h1>Manage Challenges</h1>';
    echo '<p>Here, you can add, edit, and delete challenges for MKWA Fitness members.</p>';
    // Future: Include form or table for challenge management
}

// Rewards Management Page Callback
function mkwa_manage_rewards() {
    echo '<div class="wrap"><h1>Manage Rewards</h1>';
    echo '<p>Here, you can add, edit, and delete rewards available for members to redeem.</p>';
    // Future: Include form or table for reward management
}

// Leaderboards Management Page Callback
function mkwa_manage_leaderboards() {
    echo '<div class="wrap"><h1>Leaderboards</h1>';
    echo '<p>View and reset the leaderboards for MKWA Fitness members.</p>';
    // Future: Include leaderboard display and reset functionality
}
?>
