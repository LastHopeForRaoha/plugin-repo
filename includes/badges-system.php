<?php
if (!defined('ABSPATH')) {
    exit;
}

// Award a badge to a user with additional metadata
function mkwa_award_badge($user_id, $badge_slug, $badge_name = '', $description = '', $date_awarded = null) {
    $date_awarded = $date_awarded ?: current_time('Y-m-d H:i:s');

    $badge_log = get_user_meta($user_id, 'mkwa_badge_log', true) ?: [];
    
    // Prevent duplicate badges
    foreach ($badge_log as $badge) {
        if ($badge['slug'] === $badge_slug) {
            return;
        }
    }

    $badge_log[] = [
        'slug' => $badge_slug,
        'badge' => $badge_name,
        'description' => $description,
        'date' => $date_awarded,
    ];

    update_user_meta($user_id, 'mkwa_badge_log', $badge_log);
}

// Retrieve all badges for a user
function mkwa_get_badges($user_id) {
    return get_user_meta($user_id, 'mkwa_badge_log', true) ?: [];
}

// Display badges (shortcode)
function mkwa_badges_shortcode($atts) {
    $atts = shortcode_atts(['user_id' => null], $atts, 'mkwa_badges');
    $user_id = $atts['user_id'] ?: get_current_user_id();

    if (!$user_id) {
        return '<p>Please log in to see your badges.</p>';
    }

    $badges = mkwa_get_badges($user_id);
    if (empty($badges)) {
        return '<p>You have no badges yet.</p>';
    }

    ob_start();
    echo '<div class="mkwa-badges">';
    echo '<h2>Your Badges</h2>';
    echo '<ul>';
    foreach ($badges as $badge) {
        echo '<li>';
        echo '<strong>' . esc_html($badge['badge']) . '</strong>';
        if (!empty($badge['description'])) {
            echo '<p>' . esc_html($badge['description']) . '</p>';
        }
        if (!empty($badge['date'])) {
            echo '<small>Earned on: ' . esc_html($badge['date']) . '</small>';
        }
        echo '</li>';
    }
    echo '</ul>';
    echo '</div>';
    return ob_get_clean();
}
add_shortcode('mkwa_badges', 'mkwa_badges_shortcode');

// Add admin management functions
function mkwa_admin_award_badge($user_id, $badge_slug, $badge_name, $description = '') {
    mkwa_award_badge($user_id, $badge_slug, $badge_name, $description, current_time('Y-m-d H:i:s'));
    return true;
}
