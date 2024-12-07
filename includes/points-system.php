<?php
// Ensure the file is being accessed correctly
if (!defined('ABSPATH')) {
    exit;
}

// Add points to a user with description
function mkwa_add_points($user_id, $points, $reason = '') {
    if (!class_exists('MKWAPointsSystem')) {
        return '<p>Error: Points system class is not loaded.</p>';
    }

    // Use MKWAPointsSystem to add points
    MKWAPointsSystem::add_points($user_id, $points, $reason);

    // Notify the user (optional, replace with appropriate notification system)
    if (function_exists('wp_mail')) {
        $user = get_userdata($user_id);
        $message = "You have earned $points points for the following reason: $reason.";
        wp_mail($user->user_email, 'Points Earned - MKWA Fitness', $message);
    }
}

// Deduct points from a user
function mkwa_deduct_points($user_id, $points, $reason = 'Points deduction') {
    if (!class_exists('MKWAPointsSystem')) {
        return '<p>Error: Points system class is not loaded.</p>';
    }

    MKWAPointsSystem::deduct_user_points($user_id, $points, $reason);
}

// Retrieve total points for a user
function mkwa_get_points($user_id) {
    if (!class_exists('MKWAPointsSystem')) {
        return 0;
    }

    return MKWAPointsSystem::get_user_points($user_id);
}

// Shortcode to display user points
function mkwa_points_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your points.</p>';

    $points = mkwa_get_points($user_id);
    return "<p>Your current points: <strong>{$points}</strong></p>";
}
add_shortcode('mkwa_points', 'mkwa_points_shortcode');

// Shortcode to display user points log
function mkwa_points_log_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your points log.</p>';

    $log = MKWAPointsSystem::get_user_points_log($user_id);
    if (empty($log)) return '<p>No points activity recorded yet.</p>';

    $output = '<ul>';
    foreach ($log as $entry) {
        $output .= '<li>' . esc_html($entry->date) . ': ' . esc_html($entry->points) . ' points - ' . esc_html($entry->description) . '</li>';
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('mkwa_points_log', 'mkwa_points_log_shortcode');

// Function to award bonus points for a challenge
function mkwa_award_challenge_bonus($user_id, $challenge_name) {
    $bonus_points = 200; // Default bonus for challenge completion
    $reason = "Challenge completed: $challenge_name";

    mkwa_add_points($user_id, $bonus_points, $reason);
}

// Function to reset daily streaks (scheduled daily)
function mkwa_reset_daily_streaks() {
    if (class_exists('MKWAPointsSystem')) {
        MKWAPointsSystem::reset_daily_streaks();
    }
}
add_action('mkwa_reset_streaks_daily', 'mkwa_reset_daily_streaks');

// Shortcode to display user streaks
function mkwa_user_streak_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your streaks.</p>';

    $streak_days = get_user_meta($user_id, 'mkwa_streak_days', true) ?: 0;
    return "<p>Your current streak: <strong>{$streak_days}</strong> days.</p>";
}
add_shortcode('mkwa_user_streak', 'mkwa_user_streak_shortcode');
?>
