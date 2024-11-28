<?php
// Add points to a user
function mkwa_add_points($user_id, $points, $reason = '') {
    $current_points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;
    $new_points = $current_points + $points;

    update_user_meta($user_id, 'mkwa_points', $new_points);

    // Log the transaction (optional)
    global $wpdb;
    $wpdb->insert("{$wpdb->prefix}mkwa_points_log", [
        'user_id' => $user_id,
        'points' => $points,
        'reason' => $reason,
        'date_awarded' => current_time('mysql'),
    ]);
}

// Retrieve points for a user
function mkwa_get_points($user_id) {
    return get_user_meta($user_id, 'mkwa_points', true) ?: 0;
}

// Shortcode to display user points
function mkwa_points_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your points.</p>';

    $points = mkwa_get_points($user_id);
    return "<p>Your current points: <strong>{$points}</strong></p>";
}
add_shortcode('mkwa_points', 'mkwa_points_shortcode');
?>
