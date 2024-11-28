<?php
function mkwa_manage_leaderboards() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    // Handle leaderboard reset
    if ($_POST['action'] == 'reset_leaderboard') {
        $wpdb->query("TRUNCATE TABLE $table_name");
        echo '<div class="notice notice-success"><p>Leaderboard reset successfully!</p></div>';
    }

    // Fetch leaderboard data
    $leaderboard = $wpdb->get_results(
        "SELECT user_id, points FROM $table_name ORDER BY points DESC LIMIT 50"
    );

    echo '<div class="wrap"><h1>Manage Leaderboards</h1>';
    echo '<p>View the top performers and reset the leaderboard if needed.</p>';

    // Display leaderboard
    if ($leaderboard) {
        echo '<h2>Top Performers</h2>';
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Rank</th><th>Member</th><th>Points</th></tr></thead>';
        echo '<tbody>';
        $rank = 1;
        foreach ($leaderboard as $entry) {
            $user_info = get_userdata($entry->user_id);
            $display_name = $user_info ? $user_info->display_name : 'Deleted User';
            echo "<tr><td>{$rank}</td><td>{$display_name}</td><td>{$entry->points}</td></tr>";
            $rank++;
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No leaderboard data available.</p>';
    }

    // Reset leaderboard form
    echo '<h2>Reset Leaderboard</h2>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="reset_leaderboard">';
    echo '<p><input type="submit" value="Reset Leaderboard" class="button button-primary" onclick="return confirm(\'Are you sure you want to reset the leaderboard?\');"></p>';
    echo '</form>';
    echo '</div>';
}
?>
