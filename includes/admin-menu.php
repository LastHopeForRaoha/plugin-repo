<?php
// Add MKWA Fitness menu to WordPress admin
function mkwa_admin_menu() {
    add_menu_page(
        'MKWA Fitness',              // Page title
        'MKWA Fitness',              // Menu title
        'manage_options',            // Capability
        'mkwa-fitness',              // Menu slug
        'mkwa_admin_dashboard',      // Callback function
        'dashicons-awards',          // Icon
        25                           // Position
    );

    // Add submenus
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
add_action('admin_menu', 'mkwa_admin_menu');

// Admin Dashboard Page
function mkwa_admin_dashboard() {
    echo '<div class="wrap"><h1>MKWA Fitness Admin Dashboard</h1>';
    echo '<p>Welcome to the MKWA Fitness plugin management system.</p>';
    echo '<p>Use the menu on the left to manage challenges, rewards, and leaderboards.</p></div>';
}
?>
