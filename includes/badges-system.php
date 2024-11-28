<?php
// Assign a badge to a user
function mkwa_award_badge($user_id, $badge_name) {
    $badges = get_user_meta($user_id, 'mkwa_badges', true) ?: [];
    if (!in_array($badge_name, $badges)) {
        $badges[] = $badge_name;
        update_user_meta($user_id, 'mkwa_badges', $badges);
    }
}

// Retrieve all badges for a user
function mkwa_get_badges($user_id) {
    return get_user_meta($user_id, 'mkwa_badges', true) ?: [];
}

// Display badges (shortcode)
function mkwa_badges_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your badges.</p>';

    $badges = mkwa_get_badges($user_id);
    if (empty($badges)) return '<p>You have no badges yet.</p>';

    $output = '<ul>';
    foreach ($badges as $badge) {
        $output .= "<li>{$badge}</li>";
    }
    $output .= '</ul>';

    return $output;
}
add_shortcode('mkwa_badges', 'mkwa_badges_shortcode');
?>
