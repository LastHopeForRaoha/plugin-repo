<?php
// Define levels and requirements
function mkwa_get_levels() {
    return [
        1 => ['points' => 0, 'title' => 'Aaniin', 'perk' => 'Welcome Badge'],
        2 => ['points' => 250, 'title' => 'Boozhoo', 'perk' => '5% Discount'],
        3 => ['points' => 500, 'title' => 'Mkwa Binesi', 'perk' => 'Shaker Bottle'],
        // Add more levels here...
    ];
}

// Get current level for a user
function mkwa_get_user_level($user_id) {
    $points = mkwa_get_points($user_id);
    $levels = mkwa_get_levels();

    foreach ($levels as $level => $details) {
        if ($points >= $details['points']) {
            $current_level = $level;
        }
    }
    return $levels[$current_level];
}

// Shortcode to display current level
function mkwa_level_shortcode() {
    $user_id = get_current_user_id();
    if (!$user_id) return '<p>Please log in to see your level.</p>';

    $level = mkwa_get_user_level($user_id);
    return "<p>Your current level: <strong>{$level['title']}</strong><br>Perk: {$level['perk']}</p>";
}
add_shortcode('mkwa_level', 'mkwa_level_shortcode');
?>
