<?php
/**
 * Plugin Name: MKWA Fitness Plugin
 * Description: A comprehensive plugin for MKWA Fitness, including gamification, member profiles, rewards, challenges, leaderboards, and more.
 * Version: 1.4
 * Author: MKWA Fitness Team
 * License: GPL2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enable debugging (temporary - for development only)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define constants
define('MKWA_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('MKWA_PLUGIN_URL', plugin_dir_url(__FILE__));
define('MKWA_LOG_FILE', MKWA_PLUGIN_PATH . 'mkwa-plugin-errors.log'); // Log file for errors

// Custom logging function
function mkwa_log($message) {
    $timestamp = date('Y-m-d H:i:s');
    error_log("[$timestamp] $message\n", 3, MKWA_LOG_FILE);
}

// Log plugin initialization
mkwa_log('MKWA Fitness Plugin: Initialization started.');

// Include necessary files with error logging and failure handling
$include_files = [
    'includes/member-profiles.php',
    'includes/rewards-store.php',
    'includes/challenges-system.php',
    'includes/leaderboard-system.php',
    'includes/rewards-management.php',
    'includes/leaderboard-management.php',
    'includes/admin-menu.php', // Admin menu logic is now entirely in this file.
];

foreach ($include_files as $file) {
    $file_path = MKWA_PLUGIN_PATH . $file;
    if (file_exists($file_path)) {
        include_once $file_path;
        mkwa_log("Included file: $file");
    } else {
        mkwa_log("Error: Missing required file - $file_path");
        wp_die("Required file is missing: $file_path. Please ensure all plugin files are properly installed.");
    }
}

// Enqueue assets
function mkwa_enqueue_assets() {
    wp_enqueue_style('mkwa-styles', MKWA_PLUGIN_URL . 'assets/css/styles.css', [], '1.0');
    wp_enqueue_script('mkwa-scripts', MKWA_PLUGIN_URL . 'assets/js/scripts.js', ['jquery'], '1.0', true);
    mkwa_log('Assets enqueued.');
}
add_action('wp_enqueue_scripts', 'mkwa_enqueue_assets');

// Activation hook
function mkwa_activate_plugin() {
    mkwa_log('Activation started.');

    try {
        if (function_exists('mkwa_create_rewards_table')) {
            mkwa_create_rewards_table();
            mkwa_log('Rewards table created.');
        } else {
            throw new Exception('mkwa_create_rewards_table function not found.');
        }

        if (function_exists('mkwa_create_rewards_log_table')) {
            mkwa_create_rewards_log_table();
            mkwa_log('Rewards log table created.');
        } else {
            throw new Exception('mkwa_create_rewards_log_table function not found.');
        }

        if (function_exists('mkwa_create_leaderboard_table')) {
            mkwa_create_leaderboard_table();
            mkwa_log('Leaderboard table created.');
        } else {
            throw new Exception('mkwa_create_leaderboard_table function not found.');
        }

        if (function_exists('mkwa_create_challenges_table')) {
            mkwa_create_challenges_table();
            mkwa_log('Challenges table created.');
        } else {
            throw new Exception('mkwa_create_challenges_table function not found.');
        }
    } catch (Exception $e) {
        mkwa_log('Activation error: ' . $e->getMessage());
        wp_die('Activation failed: ' . $e->getMessage());
    }

    mkwa_log('Activation completed.');
}
register_activation_hook(__FILE__, 'mkwa_activate_plugin');

// Log fatal errors on shutdown
register_shutdown_function(function () {
    $last_error = error_get_last();
    if ($last_error && in_array($last_error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        $timestamp = date('Y-m-d H:i:s');
        $error_message = "[$timestamp] Fatal Error: {$last_error['message']} in {$last_error['file']} on line {$last_error['line']}\n";
        error_log($error_message, 3, MKWA_LOG_FILE);
        wp_die("A critical error occurred. Please check the MKWA Plugin Error log for more details.");
    }
});

// Log plugin load completion
mkwa_log('MKWA Fitness Plugin: Initialization completed.');
