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

// Constants
define('MKWA_FITNESS_PLUGIN_VERSION', '1.0');
define('MKWA_FITNESS_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MKWA_FITNESS_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * Load text domain for translations.
 */
function mkwa_fitness_load_textdomain() {
    load_plugin_textdomain('mkwa-fitness-plugin', false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('plugins_loaded', 'mkwa_fitness_load_textdomain');

/**
 * Activation hook.
 */
function mkwa_fitness_activate() {
    add_option('mkwa_fitness_rewards', []);
    add_option('mkwa_fitness_challenges', []);
    add_option('mkwa_fitness_leaderboard', []);
}
register_activation_hook(__FILE__, 'mkwa_fitness_activate');

/**
 * Admin Menu for Backend Management.
 */
function mkwa_fitness_add_admin_menu() {
    add_menu_page(
        'MKWA Fitness Admin',
        'MKWA Fitness',
        'manage_options',
        'mkwa-fitness',
        'mkwa_fitness_admin_page',
        'dashicons-star-filled',
        2
    );
}
add_action('admin_menu', 'mkwa_fitness_add_admin_menu');

function mkwa_fitness_admin_page() {
    ?>
    <div class="wrap">
        <h1>MKWA Fitness Admin Panel</h1>
        <h2>Manage Rewards</h2>
        <form method="post" action="">
            <?php
            if (isset($_POST['new_reward'])) {
                $rewards = get_option('mkwa_fitness_rewards');
                $rewards[] = sanitize_text_field($_POST['new_reward']);
                update_option('mkwa_fitness_rewards', $rewards);
                echo '<p>Reward added!</p>';
            }
            ?>
            <input type="text" name="new_reward" placeholder="Enter reward name" required>
            <button type="submit">Add Reward</button>
        </form>
        <ul>
            <h3>Current Rewards</h3>
            <?php
            $rewards = get_option('mkwa_fitness_rewards', []);
            foreach ($rewards as $reward) {
                echo '<li>' . esc_html($reward) . '</li>';
            }
            ?>
        </ul>
    </div>
    <?php
}

/**
 * Shortcode: Member Dashboard
 */
function mkwa_member_dashboard_shortcode() {
    $user_id = get_current_user_id();
    $points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;

    ob_start();
    ?>
    <h2>Your Dashboard</h2>
    <p><strong>Points:</strong> <?php echo $points; ?></p>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwa_dashboard', 'mkwa_member_dashboard_shortcode');

/**
 * Shortcode: Rewards Store
 */
function mkwa_rewards_store_shortcode() {
    $user_id = get_current_user_id();
    $points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;
    $rewards = get_option('mkwa_fitness_rewards', []);

    ob_start();
    ?>
    <h2>Rewards Store</h2>
    <p>You have <strong><?php echo $points; ?></strong> points.</p>
    <ul>
        <?php foreach ($rewards as $reward) : ?>
            <li>
                <?php echo esc_html($reward); ?>
                <?php if ($points >= 100) : ?>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="redeem_reward" value="<?php echo esc_attr($reward); ?>">
                        <button type="submit">Redeem</button>
                    </form>
                <?php else : ?>
                    <span>Not enough points</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
    if (isset($_POST['redeem_reward'])) {
        $reward = sanitize_text_field($_POST['redeem_reward']);
        update_user_meta($user_id, 'mkwa_points', $points - 100);
        echo '<p>Redeemed ' . esc_html($reward) . '!</p>';
    }
    return ob_get_clean();
}
add_shortcode('mkwa_rewards_store', 'mkwa_rewards_store_shortcode');

/**
 * Shortcode: Challenges
 */
function mkwa_active_challenges_shortcode() {
    $challenges = get_option('mkwa_fitness_challenges', []);

    ob_start();
    ?>
    <h2>Active Challenges</h2>
    <ul>
        <?php foreach ($challenges as $challenge) : ?>
            <li><?php echo esc_html($challenge); ?></li>
        <?php endforeach; ?>
    </ul>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwa_challenges', 'mkwa_active_challenges_shortcode');

/**
 * Shortcode: Leaderboard
 */
function mkwa_leaderboard_shortcode() {
    $leaderboard = get_option('mkwa_fitness_leaderboard', []);
    arsort($leaderboard);

    ob_start();
    ?>
    <h2>Leaderboard</h2>
    <ol>
        <?php foreach ($leaderboard as $user_id => $points) : ?>
            <li><?php echo esc_html(get_userdata($user_id)->display_name); ?> - <?php echo esc_html($points); ?> points</li>
        <?php endforeach; ?>
    </ol>
    <?php
    return ob_get_clean();
}
add_shortcode('mkwa_leaderboard', 'mkwa_leaderboard_shortcode');
