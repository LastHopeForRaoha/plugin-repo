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

    /**
     * Display the user dashboard.
     */
    public static function display_dashboard($atts) {
        if (!is_user_logged_in()) {
            return '<p>Please log in to view your dashboard.</p>';
        }

        $user_id = get_current_user_id();
        $profile = mkwa_get_profile_data($user_id);
        $level = mkwa_get_user_level($user_id);
        $badges = MKWABadgesSystem::get_user_badges($user_id);
        $quests = MKWADailyQuests::get_user_quests($user_id);
        $history = MKWAActivityHistory::get_user_history($user_id);

        ob_start();
        ?>
        <div class="mkwa-dashboard">
            <h2>Welcome, <?php echo esc_html($profile['display_name'] ?: 'Member'); ?></h2>
            <img src="<?php echo esc_url(get_user_meta($user_id, 'profile_picture', true) ?: plugin_dir_url(__FILE__) . 'assets/default-avatar.png'); ?>" alt="Profile Picture" class="profile-picture">
            <p><strong>Bio:</strong> <?php echo esc_html($profile['bio'] ?: 'No bio provided.'); ?></p>

            <div class="progress-section">
                <h3>Your Progress</h3>
                <p><strong>Total Points:</strong> <?php echo esc_html($profile['points'] ?: 0); ?></p>
                <p><strong>Current Level:</strong> <?php echo esc_html($level['title'] ?: 'N/A'); ?></p>
                <p><strong>Next Level:</strong> <?php echo esc_html($level['next_title'] ?: 'N/A'); ?> (<?php echo esc_html($level['points_to_next'] ?: 0); ?> points to go)</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo esc_attr($level['progress_percent'] ?: 0); ?>%;"></div>
                </div>
            </div>

            <div class="badges-section">
                <h3>Your Badges</h3>
                <?php if (!empty($badges)) : ?>
                    <ul>
                        <?php foreach ($badges as $badge) : ?>
                            <li><?php echo esc_html($badge['name'] ?: 'Unnamed Badge'); ?> - <?php echo esc_html($badge['description'] ?: 'No description.'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No badges earned yet. Start completing activities to earn some!</p>
                <?php endif; ?>
            </div>

            <div class="quests-section">
                <h3>Daily Quests</h3>
                <?php if (!empty($quests)) : ?>
                    <ul>
                        <?php foreach ($quests as $quest) : ?>
                            <li><?php echo esc_html($quest->quest_name ?: 'Unknown Quest'); ?> - <?php echo $quest->completed ? 'Completed' : 'Incomplete'; ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <p>No quests available today. Check back tomorrow!</p>
                <?php endif; ?>
            </div>

            <div class="history-section">
                <h3>Activity History</h3>
                <ul>
                    <?php if (!empty($history)) : ?>
                        <?php foreach ($history as $event) : ?>
                            <li><?php echo esc_html($event['date'] ?: 'N/A'); ?> - <?php echo esc_html($event['description'] ?: 'No description.'); ?></li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No activity history yet.</p>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="overall-score-section">
                <h3>Overall Member Score</h3>
                <p>Your Overall Score: <?php echo esc_html(self::calculate_overall_score($user_id) ?: 0); ?></p>
            </div>

            <div class="profile-edit-section">
                <h3>Edit Profile</h3>
                <form id="mkwa-profile-form" method="POST" enctype="multipart/form-data">
                    <label for="profile-picture">Upload Profile Picture:</label>
                    <input type="file" id="profile-picture" name="profile_picture">
                    <label for="mkwa-bio">Update Bio:</label>
                    <textarea id="mkwa-bio" name="mkwa_bio"><?php echo esc_attr($profile['bio']); ?></textarea>
                    <?php wp_nonce_field('mkwa_update_profile_nonce', 'mkwa_profile_nonce'); ?>
                    <button type="submit">Update Profile</button>
                </form>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle AJAX profile update.
     */
    public static function update_profile() {
        check_ajax_referer('mkwa_update_profile_nonce', 'mkwa_profile_nonce');

        if (!is_user_logged_in()) {
            wp_send_json_error('You must be logged in to update your profile.');
        }

        $user_id = get_current_user_id();
        $bio = sanitize_textarea_field($_POST['mkwa_bio']);
        $profile_picture = $_FILES['profile_picture'];

        if ($profile_picture && !empty($profile_picture['tmp_name'])) {
            $upload = wp_handle_upload($profile_picture, ['test_form' => false]);
            if (isset($upload['url'])) {
                update_user_meta($user_id, 'profile_picture', $upload['url']);
            }
        }

        update_user_meta($user_id, 'mkwa_bio', $bio);
        wp_send_json_success('Profile updated successfully.');
    }

    /**
     * Calculate overall member score.
     */
    public static function calculate_overall_score($user_id) {
        $weights = [
            'attendance' => 0.3,
            'challenge_completion' => 0.2,
            'community_participation' => 0.15,
            'streak_maintenance' => 0.2,
            'point_earning_rate' => 0.15,
        ];
        $metrics = MKWAActivityHistory::gather_user_metrics($user_id);
        $overall_score = 0;

        foreach ($weights as $category => $weight) {
            $overall_score += ($metrics[$category] ?? 0) * $weight;
        }

        return round($overall_score, 2);
    }
}

MKWADashboard::init();
