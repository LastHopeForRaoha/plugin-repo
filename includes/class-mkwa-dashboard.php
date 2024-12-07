<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWADashboard {
    public static function init() {
        // Register shortcode for the dashboard
        add_shortcode('mkwa_dashboard', [__CLASS__, 'display_dashboard']);
        // Add AJAX handler for profile updates
        add_action('wp_ajax_mkwa_update_profile', [__CLASS__, 'update_profile']);
    }

    public static function display_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your dashboard.</p>';
        }

        $user_id = get_current_user_id();
        $points = MKWAPointsSystem::get_user_points($user_id);
        $badges = MKWABadgesSystem::get_user_badges($user_id);
        $quests = MKWADailyQuests::get_user_quests($user_id);

        ob_start();
        ?>
        <div class="mkwa-dashboard">
            <h2>Your Dashboard</h2>
            <p>Points: <?php echo esc_html($points); ?></p>

            <h3>Badges</h3>
            <?php if (!empty($badges)) : ?>
                <ul>
                    <?php foreach ($badges as $badge) : ?>
                        <li><?php echo esc_html($badge->name); ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No badges earned yet.</p>
            <?php endif; ?>

            <h3>Daily Quests</h3>
            <?php if (!empty($quests)) : ?>
                <ul>
                    <?php foreach ($quests as $quest) : ?>
                        <li><?php echo esc_html($quest->quest_name); ?> - <?php echo $quest->completed ? 'Completed' : 'Incomplete'; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p>No quests assigned.</p>
            <?php endif; ?>

            <h3>Profile</h3>
            <form id="mkwa-profile-form">
                <label for="mkwa-display-name">Display Name:</label>
                <input type="text" id="mkwa-display-name" name="display_name" value="<?php echo esc_attr(get_user_meta($user_id, 'display_name', true)); ?>">
                <button type="submit">Update Profile</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    }

    public static function update_profile() {
        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to update your profile.');
        }

        $user_id = get_current_user_id();
        $display_name = sanitize_text_field($_POST['display_name']);

        if (empty($display_name)) {
            wp_send_json_error('Display name cannot be empty.');
        }

        wp_update_user([
            'ID' => $user_id,
            'display_name' => $display_name,
        ]);

        update_user_meta($user_id, 'display_name', $display_name);

        wp_send_json_success('Profile updated successfully.');
    }
}
