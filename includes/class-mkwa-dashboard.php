<?php
/**
 * MKWA Fitness - Member Dashboard
 * Handles the functionality for the Member Dashboard.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MKWA_Dashboard {
    public function __construct() {
        // Register shortcode.
        add_shortcode('mkwa_dashboard', [$this, 'render_dashboard']);

        // Add AJAX handlers for profile updates.
        add_action('wp_ajax_mkwa_update_profile', [$this, 'update_profile']);
    }

    /**
     * Render the Member Dashboard.
     */
    public function render_dashboard() {
        // Ensure the user is logged in.
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your dashboard.</p>';
        }

        $user_id = get_current_user_id();
        $points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;
        $badges = get_user_meta($user_id, 'mkwa_badges', true) ?: [];
        $referrals = get_user_meta($user_id, 'mkwa_referrals', true) ?: [];
        $avatar_url = get_user_meta($user_id, 'mkwa_avatar', true) ?: get_avatar_url($user_id);
        $username = get_user_meta($user_id, 'mkwa_username', true) ?: wp_get_current_user()->display_name;

        ob_start();
        ?>
        <div class="mkwa-dashboard">
            <h2>Welcome, <?php echo esc_html($username); ?></h2>
            <div class="mkwa-profile">
                <img src="<?php echo esc_url($avatar_url); ?>" alt="Avatar" class="mkwa-avatar">
                <button id="mkwa-edit-profile">Edit Profile</button>
            </div>
            <div class="mkwa-stats">
                <p><strong>Points:</strong> <?php echo esc_html($points); ?></p>
                <p><strong>Badges:</strong> <?php echo esc_html(implode(', ', $badges)); ?></p>
                <p><strong>Referrals:</strong> <?php echo esc_html(count($referrals)); ?> successful</p>
            </div>
            <div class="mkwa-level-progress">
                <h3>Your Level Progress</h3>
                <?php $this->render_progress_bar($points); ?>
            </div>
            <div class="mkwa-leaderboard-optout">
                <label>
                    <input type="checkbox" id="mkwa-optout-leaderboard" <?php checked(get_user_meta($user_id, 'mkwa_leaderboard_optout', true), true); ?>>
                    Opt out of Leaderboard
                </label>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render the progress bar for leveling.
     */
    private function render_progress_bar($points) {
        $level = floor($points / 100);
        $progress = $points % 100;

        ?>
        <div class="progress-bar-container">
            <div class="progress-bar" style="width: <?php echo esc_attr($progress); ?>%;"></div>
        </div>
        <p>Level <?php echo esc_html($level); ?> (<?php echo esc_html($progress); ?>/100 points to next level)</p>
        <?php
    }

    /**
     * Handle AJAX request for profile updates.
     */
    public function update_profile() {
        check_ajax_referer('mkwa_profile_update', 'security');

        $user_id = get_current_user_id();

        if (isset($_POST['username'])) {
            update_user_meta($user_id, 'mkwa_username', sanitize_text_field($_POST['username']));
        }

        if (isset($_POST['avatar_url'])) {
            update_user_meta($user_id, 'mkwa_avatar', esc_url_raw($_POST['avatar_url']));
        }

        wp_send_json_success(['message' => 'Profile updated successfully!']);
    }
}

// Initialize the class.
new MKWA_Dashboard();
