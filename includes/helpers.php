<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Get profile data for a user.
 *
 * @param int $user_id The user ID.
 * @return array An array containing user profile data.
 */
function mkwa_get_profile_data($user_id) {
    $user = get_userdata($user_id);

    if (!$user) {
        return [
            'display_name' => 'Anonymous',
            'bio' => 'This user does not exist.',
            'points' => 0,
        ];
    }

    return [
        'display_name' => $user->display_name ?: 'Member',
        'bio' => get_user_meta($user_id, 'mkwa_bio', true) ?: 'No bio provided.',
        'points' => get_user_meta($user_id, 'mkwa_total_points', true) ?: 0,
    ];
}

/**
 * Format points for display.
 *
 * @param int $points The points value.
 * @return string The formatted points.
 */
function mkwa_format_points($points) {
    return number_format($points) . ' pts';
}

/**
 * Fetch user badges in a readable format.
 *
 * @param int $user_id The user ID.
 * @return array A list of user badges.
 */
function mkwa_get_user_badges($user_id) {
    if (class_exists('MKWABadgesSystem')) {
        return MKWABadgesSystem::get_user_badges($user_id);
    }

    return [];
}

/**
 * Helper function to calculate user progress towards a goal.
 *
 * @param int $current The current value.
 * @param int $goal The goal value.
 * @return int The percentage of progress.
 */
function mkwa_calculate_progress($current, $goal) {
    if ($goal <= 0) {
        return 0;
    }

    return min(100, round(($current / $goal) * 100));
}
