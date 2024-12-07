
<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWABadgesSystem {
    public static function init() {
        // Hook into points system activity to check badge eligibility.
        add_action('mkwa_points_activity', [__CLASS__, 'check_badge_awards'], 10, 2);
        // Add admin menu for badge management.
        add_action('admin_menu', [__CLASS__, 'add_admin_menu']);
    }

    /**
     * Check badge awards based on user activity.
     */
    public static function check_badge_awards($user_id, $activity) {
        $badges = self::get_user_badges($user_id);

        // Iron Bear: 100 Check-ins.
        if ($activity === 'checkin') {
            self::assign_badge($user_id, 'iron_bear', $badges, 100, "Iron Bear", "100 gym check-ins");
        }

        // 7-Day Warrior: 7 consecutive days of attendance.
        if ($activity === 'attendance_streak' && self::check_streak($user_id, 7)) {
            self::assign_badge($user_id, '7_day_warrior', $badges, 50, "7-Day Warrior", "7 consecutive days of attendance");
        }

        // Social Bear: Referrals.
        if ($activity === 'referral' && self::get_referral_count($user_id) >= 5) {
            self::assign_badge($user_id, 'social_bear', $badges, 200, "Social Bear", "Referred 5 members");
        }

        // Seasonal Champion: Seasonal Challenges.
        if ($activity === 'seasonal_challenge') {
            self::assign_badge($user_id, 'seasonal_champion', $badges, 300, "Seasonal Champion", "Completed a seasonal challenge");
        }
        
        // Additional badge logic for class attendance, leaderboard ranks, and lifetime points.
        if ($activity === 'class_attendance' && self::get_class_count($user_id) >= 50) {
            self::assign_badge($user_id, 'class_master', $badges, 150, "Class Master", "Attended 50 classes");
        }

        if ($activity === 'lifetime_points' && self::get_user_points($user_id) >= 5000) {
            self::assign_badge($user_id, 'point_legend', $badges, 500, "Point Legend", "Earned 5000 lifetime points");
        }
    }

    /**
     * Assign a badge if not already assigned.
     */
    private static function assign_badge($user_id, $badge_slug, $current_badges, $bonus_points, $badge_name, $description) {
        if (in_array($badge_slug, $current_badges)) {
            return;
        }

        // Add badge to user's badge list.
        $current_badges[] = $badge_slug;
        update_user_meta($user_id, 'mkwa_badges', $current_badges);

        // Add bonus points for earning the badge.
        MKWAPointsSystem::add_points($user_id, $bonus_points, "Awarded badge: {$badge_name}");

        // Notify user of the new badge.
        self::notify_user($user_id, $badge_name, $description);
    }

    /**
     * Get user badges.
     */
    public static function get_user_badges($user_id) {
        return get_user_meta($user_id, 'mkwa_badges', true) ?: [];
    }

    /**
     * Notify user about badge award.
     */
    private static function notify_user($user_id, $badge_name, $description) {
        $user = get_userdata($user_id);
        $message = "Congratulations! You've earned the '{$badge_name}' badge: {$description}.";
        wp_mail($user->user_email, "New Badge Earned!", $message);
    }

    /**
     * Check user attendance streak.
     */
    private static function check_streak($user_id, $days) {
        // Logic to verify attendance streak of $days.
        return true; // Placeholder for actual streak logic.
    }

    /**
     * Get referral count.
     */
    private static function get_referral_count($user_id) {
        // Fetch referral count from the database.
        return 5; // Placeholder.
    }

    /**
     * Add admin menu for badge management.
     */
    public static function add_admin_menu() {
        add_submenu_page(
            'mkwa-fitness',
            'Badge Management',
            'Manage Badges',
            'manage_options',
            'mkwa-badges',
            [__CLASS__, 'render_admin_page']
        );
    }

    public static function render_admin_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['badge_slug'])) {
            $badge_slug = sanitize_text_field($_POST['badge_slug']);
            $badge_name = sanitize_text_field($_POST['badge_name']);
            $description = sanitize_textarea_field($_POST['description']);
            $bonus_points = intval($_POST['bonus_points']);
            // Save the badge to a configuration list (future feature).
        }

        // Display badge management interface.
        echo '<h1>Manage Badges</h1>';
        echo '<form method="POST">';
        echo '<label for="badge_slug">Badge Slug:</label><input type="text" id="badge_slug" name="badge_slug" required>';
        echo '<label for="badge_name">Badge Name:</label><input type="text" id="badge_name" name="badge_name" required>';
        echo '<label for="description">Description:</label><textarea id="description" name="description" required></textarea>';
        echo '<label for="bonus_points">Bonus Points:</label><input type="number" id="bonus_points" name="bonus_points" required>';
        echo '<button type="submit">Add Badge</button>';
        echo '</form>';
    }
}
?>
