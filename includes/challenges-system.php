<?php
// Fetch active challenges
function mkwa_get_active_challenges() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_challenges';

    $today = current_time('mysql');
    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_name WHERE start_date <= %s AND end_date >= %s",
            $today,
            $today
        )
    );
}

// Complete a challenge and award points
function mkwa_complete_challenge($user_id, $challenge_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_challenges';

    // Sanitize user ID
    $user_id = intval($user_id);

    // Fetch challenge details
    $challenge = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $challenge_id)
    );

    // Validate challenge and dates
    if (!$challenge) {
        error_log("Challenge with ID $challenge_id not found.");
        return false;
    }

    $current_time = current_time('mysql');
    if ($current_time < $challenge->start_date || $current_time > $challenge->end_date) {
        error_log("Challenge with ID $challenge_id is not active.");
        return false;
    }

    // Award points for the challenge
    mkwa_add_points($user_id, $challenge->points, "Completed challenge: {$challenge->name}");

    return true;
}

// Display active challenges
function mkwa_display_active_challenges() {
    $challenges = mkwa_get_active_challenges();

    if (empty($challenges)) {
        echo '<p>No active challenges.</p>';
        return;
    }

    echo '<h2>Active Challenges</h2>';
    echo '<ul>';
    foreach ($challenges as $challenge) {
        echo sprintf(
            '<li><strong>%s:</strong> %s - %d points</li>',
            esc_html($challenge->name),
            esc_html($challenge->description),
            intval($challenge->points)
        );
    }
    echo '</ul>';
}

// Shortcode for displaying active challenges
function mkwa_active_challenges_shortcode() {
    ob_start();
    mkwa_display_active_challenges();
    return ob_get_clean();
}
add_shortcode('mkwa_challenges', 'mkwa_active_challenges_shortcode');
?>
