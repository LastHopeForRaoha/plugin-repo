<?php
/*
Plugin Name: MKWA Fitness Plugin
Plugin URI: https://example.com/mkwa-fitness-plugin
Description: A custom plugin for MKWA Fitness to manage features and integrations.
Version: 1.0
Author: Your Name
Author URI: https://example.com
Text Domain: mkwa-fitness-plugin
Domain Path: /languages
License: GPL2
*/

if (!defined('ABSPATH')) {
    exit; // Prevent direct access to the file.
}

// Define constants
define('MKWA_FITNESS_PLUGIN_VERSION', '1.0');
define('MKWA_FITNESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MKWA_FITNESS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load plugin text domain for translations.
 */
function mkwa_fitness_load_textdomain() {
    load_plugin_textdomain('mkwa-fitness-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mkwa_fitness_load_textdomain');

/**
 * Register activation hook.
 */
function mkwa_fitness_activate() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Add default options
    add_option('mkwa_fitness_settings', [
        'welcome_message' => 'Welcome to MKWA Fitness!',
    ]);
}
register_activation_hook(__FILE__, 'mkwa_fitness_activate');

/**
 * Register deactivation hook.
 */
function mkwa_fitness_deactivate() {
    if (!current_user_can('activate_plugins')) {
        return;
    }

    // Cleanup options
    delete_option('mkwa_fitness_settings');
}
register_deactivation_hook(__FILE__, 'mkwa_fitness_deactivate');

/**
 * Initialize plugin functionality.
 */
function mkwa_fitness_plugin_init() {
    // Add initialization logic here if needed.
}
add_action('init', 'mkwa_fitness_plugin_init');

/**
 * Add admin menu page.
 */
function mkwa_fitness_add_admin_menu() {
    add_menu_page(
        'MKWA Fitness Settings',  // Page title
        'MKWA Fitness',           // Menu title
        'manage_options',         // Capability
        'mkwa-fitness',           // Menu slug
        'mkwa_fitness_settings_page', // Callback function
        'dashicons-heart',        // Icon
        2                         // Position
    );
}
add_action('admin_menu', 'mkwa_fitness_add_admin_menu');

/**
 * Admin settings page content.
 */
function mkwa_fitness_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Save settings if submitted
    if (isset($_POST['mkwa_fitness_save_settings'])) {
        check_admin_referer('mkwa_fitness_save_settings');
        $welcome_message = sanitize_text_field($_POST['mkwa_fitness_welcome_message']);
        update_option('mkwa_fitness_settings', ['welcome_message' => $welcome_message]);
        echo '<div class="notice notice-success"><p>Settings saved successfully!</p></div>';
    }

    $settings = get_option('mkwa_fitness_settings');
    ?>
    <div class="wrap">
        <h1>MKWA Fitness Plugin Settings</h1>
        <form method="post" action="">
            <?php wp_nonce_field('mkwa_fitness_save_settings'); ?>
            <table class="form-table">
                <tr>
                    <th><label for="mkwa_fitness_welcome_message">Welcome Message</label></th>
                    <td>
                        <input type="text" id="mkwa_fitness_welcome_message" name="mkwa_fitness_welcome_message" 
                               value="<?php echo esc_attr($settings['welcome_message']); ?>" class="regular-text">
                    </td>
                </tr>
            </table>
            <p class="submit">
                <button type="submit" name="mkwa_fitness_save_settings" class="button button-primary">Save Settings</button>
            </p>
        </form>
    </div>
    <?php
}

/**
 * Add shortcodes for plugin functionality.
 */
function mkwa_register_shortcodes() {
    // Member dashboard
    add_shortcode('mkwa_dashboard', 'mkwa_member_dashboard_shortcode');
    // Rewards store
    add_shortcode('mkwa_rewards_store', 'mkwa_rewards_store_shortcode');
    // Challenges
    add_shortcode('mkwa_challenges', 'mkwa_active_challenges_shortcode');
    // Leaderboard
    add_shortcode('mkwa_leaderboard', 'mkwa_leaderboard_shortcode');
    // Welcome message (new)
    add_shortcode('mkwa_fitness_welcome', 'mkwa_fitness_welcome_message_shortcode');
}
add_action('init', 'mkwa_register_shortcodes');

/**
 * Shortcode callback for the welcome message.
 */
function mkwa_fitness_welcome_message_shortcode() {
    $settings = get_option('mkwa_fitness_settings');
    return '<p>' . esc_html($settings['welcome_message']) . '</p>';
}

/**
 * Shortcode callback for the member dashboard.
 */
function mkwa_member_dashboard_shortcode() {
    return '<h2>Member Dashboard</h2><p>This is the MKWA Fitness member dashboard.</p>';
}

/**
 * Shortcode callback for the rewards store.
 */
function mkwa_rewards_store_shortcode() {
    return '<h2>Rewards Store</h2><p>Here you can redeem points for rewards.</p>';
}

/**
 * Shortcode callback for active challenges.
 */
function mkwa_active_challenges_shortcode() {
    return '<h2>Active Challenges</h2><p>Participate in the latest fitness challenges!</p>';
}

/**
 * Shortcode callback for the leaderboard.
 */
function mkwa_leaderboard_shortcode() {
    return '<h2>Leaderboard</h2><p>See the top members on the leaderboard.</p>';
}
