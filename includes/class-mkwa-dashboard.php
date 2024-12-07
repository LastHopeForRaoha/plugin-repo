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

        // Fetch user profile data
        $profile = [
            'display_name' => get_user_meta($user_id, 'display_name', true) ?: wp_get_current_user()->display_name,
            'bio' => get_user_meta($user_id, 'mkwa_bio', true),
            'points' => MKWAPointsSystem::get_user_points($user_id),
        ];

        $level = self::get_user_level($profile['points']);
        $badges = MKWABadgesSystem::get_user_badges($user_id);
        $quests = MKWADailyQuests::get_user_quests($user_id);
        $history = MKWAActivityHistory::get_user_history($user_id);
        $ultimate_score = self::calculate_ultimate_score($user_id); // New feature

        ob_start();
        ?>
        <div class="mkwa-dashboard">
            <!-- Profile Section -->
            <div class="dashboard-profile">
                <img src="<?php echo esc_url(get_user_meta($user_id, 'profile_picture', true) ?: plugin_dir_url(__FILE__) . 'assets/default-avatar.png'); ?>" alt="Profile Picture" class="profile-picture">
                <h2><?php echo esc_html($profile['display_name'] ?: 'Member'); ?></h2>
                <p><strong>Bio:</strong> <?php echo esc_html($profile['bio'] ?: 'No bio provided.'); ?></p>
                <form id="mkwa-profile-form" method="POST" enctype="multipart/form-data">
                    <input type="file" id="profile-picture" name="profile_picture">
                    <textarea id="mkwa-bio" name="mkwa_bio"><?php echo esc_textarea($profile['bio']); ?></textarea>
                    <?php wp_nonce_field('mkwa_update_profile_nonce', 'mkwa_profile_nonce'); ?>
                    <button type="submit">Update Profile</button>
                </form>
            </div>

            <!-- Progress Section -->
            <div class="dashboard-progress">
                <h3>Your Progress</h3>
                <p><strong>Total Points:</strong> <?php echo esc_html($profile['points']); ?></p>
                <p><strong>Current Level:</strong> <?php echo esc_html($level['title']); ?></p>
                <p><strong>Next Level:</strong> <?php echo esc_html($level['next_title']); ?> (<?php echo esc_html($level['points_to_next']); ?> points to go)</p>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo esc_attr($level['progress_percent']); ?>%;"></div>
                </div>
            </div>

            <!-- Ultimate Score -->
            <div class="dashboard-ultimate-score">
                <h3>Your Ultimate Score</h3>
                <p><?php echo esc_html($ultimate_score); ?></p>
            </div>

            <!-- Badges Section -->
            <div class="dashboard-badges">
                <h3>Your Badges</h3>
                <?php if (!empty($badges)) : ?>
                    <div class="badge-grid">
                        <?php foreach ($badges as $badge) : ?>
                            <div class="badge-item">
                                <img src="<?php echo esc_url($badge['icon_url']); ?>" alt="<?php echo esc_attr($badge['name']); ?>">
                                <span class="badge-tooltip"><?php echo esc_html($badge['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>No badges earned yet. Start completing activities to earn some!</p>
                <?php endif; ?>
            </div>

            <!-- Daily Quests Section -->
            <div class="dashboard-quests">
                <h3>Daily Quests</h3>
                <?php if (!empty($quests)) : ?>
                    <div class="quest-list">
                        <?php foreach ($quests as $quest) : ?>
                            <div class="quest-card">
                                <h4><?php echo esc_html($quest->quest_name); ?></h4>
                                <p><?php echo esc_html($quest->progress . '/' . $quest->goal); ?> completed</p>
                                <div class="quest-progress-bar">
                                    <div class="quest-progress-fill" style="width: <?php echo ($quest->progress / $quest->goal) * 100; ?>%;"></div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else : ?>
                    <p>No quests available today. Check back tomorrow!</p>
                <?php endif; ?>
            </div>

            <!-- Activity History Section -->
            <div class="dashboard-history">
                <h3>Activity History</h3>
                <ul>
                    <?php if (!empty($history)) : ?>
                        <?php foreach ($history as $event) : ?>
                            <li><?php echo esc_html($event['date']); ?> - <?php echo esc_html($event['description']); ?></li>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <p>No activity history yet.</p>
                    <?php endif; ?>
                </ul>
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
     * Fetch user level details.
     */
    private static function get_user_level($points) {
        $levels = [
            ['title' => 'Beginner', 'points' => 0],
            ['title' => 'Intermediate', 'points' => 500],
            ['title' => 'Advanced', 'points' => 1000],
        ];

        $current = $levels[0];
        $next = $levels[1];
        $progress = 0;

        foreach ($levels as $index => $level) {
            if ($points >= $level['points']) {
                $current = $level;
                $next = $levels[$index + 1] ?? null;
            }
        }

        if ($next) {
            $progress = (($points - $current['points']) / ($next['points'] - $current['points'])) * 100;
        }

        return [
            'title' => $current['title'],
            'next_title' => $next['title'] ?? 'Max Level',
            'points_to_next' => $next['points'] - $points ?? 0,
            'progress_percent' => $progress,
        ];
    }

    /**
     * Calculate the user's Ultimate Score.
     */
    private static function calculate_ultimate_score($user_id) {
        $points = MKWAPointsSystem::get_user_points($user_id);
        $badges = count(MKWABadgesSystem::get_user_badges($user_id));
        $days_as_member = (time() - strtotime(get_userdata($user_id)->user_registered)) / 86400;

        return round($points + ($badges * 100) + ($days_as_member * 10));
    }
}

MKWADashboard::init();
