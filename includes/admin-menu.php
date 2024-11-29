<?php
/**
 * MKWA Fitness - Admin Menu
 * Handles admin menu creation and related functions.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Register admin menu
function mkwa_register_admin_menu() {
    mkwa_log('Registering admin menu...');
    add_menu_page(
        'MKWA Fitness',              // Page title
        'MKWA Fitness',              // Menu title
        'manage_options',            // Capability
        'mkwa-fitness',              // Menu slug
        'mkwa_admin_dashboard',      // Callback function
        'dashicons-awards',          // Icon
        6                            // Position
    );
    mkwa_log('Admin menu registered.');
}
add_action('admin_menu', 'mkwa_register_admin_menu');

// Admin dashboard content
function mkwa_admin_dashboard() {
    mkwa_log('Admin dashboard loaded.');
    echo '<div class="wrap"><h1>Welcome to MKWA Fitness Plugin</h1>';
    echo '<p>This is the admin dashboard for managing rewards, challenges, and leaderboards.</p></div>';
}
