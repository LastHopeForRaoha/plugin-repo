<?php
/**
 * MKWA Fitness Level System
 * Handles user level progression, badge awards, and notifications based on points.
 */

// Define levels and requirements based on MKWA Fitness Gamification System
function mkwa_get_levels() {
    return [
        1 => ['points' => 0, 'title' => 'Aaniin', 'perk' => 'Welcome Badge', 'badge' => 'Aaniin'],
        2 => ['points' => 250, 'title' => 'Boozhoo', 'perk' => '5% Discount on Merchandise', 'badge' => 'Boozhoo'],
        3 => ['points' => 500, 'title' => 'Mkwa Binesi', 'perk' => 'Free Shaker Bottle', 'badge' => 'Iron Bear'],
        4 => ['points' => 1000, 'title' => 'Zhooniyaa Wiijii', 'perk' => 'Exclusive Badge + Free Class Credit', 'badge' => 'Trailblazer'],
        5 => ['points' => 2000, 'title' => 'Zaagidwin', 'perk' => 'Free T-Shirt + Leaderboard Recognition', 'badge' => 'Zaagidwin'],
        6 => ['points' => 3500, 'title' => 'Gchi-Mkwa', 'perk' => 'Free Baseball Cap + Event Priority Access', 'badge' => 'Seasonal Strength'],
        7 => ['points' => 5000, 'title' => 'Manidoo Miikana', 'perk' => '10% Discount on Membership & Merchandise (1 Month)', 'badge' => 'Community Builder'],
        8 => ['points' => 7500, 'title' => 'Wiigwaas Zhigaawin', 'perk' => 'Exclusive Hoodie + Premium Perks', 'badge' => 'Transformation Hero'],
        9 => ['points' => 10000, 'title' => 'Gchi-Binesi', 'perk' => 'Social Media Spotlight + Hall of Fame Recognition', 'badge' => '500-Workout Champion'],
        10 => ['points' => 15000, 'title' => 'Anishinaabe Giizhigong', 'perk' => 'Premium Perks + Custom Trophy', 'badge' => 'Eagle Vision'],
    ];
}

// Get the current level for a user
function mkwa_get_user_level($user_id) {
    $points = MKWAPointsSystem::get_user_points($user_id); // Fetch user's total points
    $levels = mkwa_get_levels();
    $current_level = 1;

    foreach ($levels as $level => $details) {
        if ($points >= $details['points']) {
            $current_level = $level;
        }
    }
    return $levels[$current_level];
}

// Shortcode to display current level and perks
function mkwa_level_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your level.</p>';

    $level = mkwa_get_user_level($user_id);
    return "<p>Your current level: <strong>{$level['title']}</strong><br>Perk: {$level['perk']}</p>";
}
add_shortcode('mkwa_level', 'mkwa_level_shortcode');

// Award badges and perks based on level progression
function mkwa_award_level_badge($user_id) {
    $level = mkwa_get_user_level($user_id);
    mkwa_award_badge($user_id, $level['badge']); // Award corresponding badge
}

// Notify user of level progression
function mkwa_notify_user_of_level($user_id, $level) {
    $user_info = get_userdata($user_id);
    if ($user_info) {
        wp_mail(
            $user_info->user_email,
            "Congratulations on Leveling Up!",
            "Hi {$user_info->display_name},\n\nYou've reached Level {$level['title']}! Enjoy your new perk: {$level['perk']}.\n\nKeep up the great work at MKWA Fitness!\n\nBest regards,\nThe MKWA Fitness Team"
        );
    }
}

// Hook to update level, award badge, and notify user upon point addition
add_action('mkwa_points_updated', function($user_id) {
    $level = mkwa_get_user_level($user_id);
    mkwa_award_level_badge($user_id);
    mkwa_notify_user_of_level($user_id, $level);
});

// Display user's progress to the next level
function mkwa_get_progress_to_next_level($user_id) {
    $points = MKWAPointsSystem::get_user_points($user_id);
    $levels = mkwa_get_levels();
    $next_level_points = 0;
    $next_level_title = '';

    foreach ($levels as $level => $details) {
        if ($points < $details['points']) {
            $next_level_points = $details['points'];
            $next_level_title = $details['title'];
            break;
        }
    }

    if ($next_level_points > 0) {
        $progress = round(($points / $next_level_points) * 100, 2);
        return [
            'title' => $next_level_title,
            'points_needed' => $next_level_points - $points,
            'progress_percentage' => $progress,
        ];
    }

    return ['title' => 'Max Level', 'points_needed' => 0, 'progress_percentage' => 100];
}

// Shortcode to display user's progress to the next level
function mkwa_next_level_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your progress.</p>';

    $progress = mkwa_get_progress_to_next_level($user_id);

    if ($progress['title'] === 'Max Level') {
        return '<p>You’ve reached the maximum level! Keep up the great work!</p>';
    }

    return "<p>Next Level: <strong>{$progress['title']}</strong><br>
            Points Needed: {$progress['points_needed']}<br>
            Progress: {$progress['progress_percentage']}%</p>";
}
add_shortcode('mkwa_next_level', 'mkwa_next_level_shortcode');
