<?php
// Create leaderboard table
function mkwa_create_leaderboard_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        points int NOT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Shortcode to display the leaderboard
function mkwa_leaderboard_shortcode() {
    ob_start();
    mkwa_display_leaderboard();
    return ob_get_clean();
}
add_shortcode('mkwa_leaderboard', 'mkwa_leaderboard_shortcode');

// Display leaderboard data
function mkwa_display_leaderboard() {
    $top_performers = mkwa_get_top_performers();

    if (empty($top_performers)) {
        echo '<p>No leaderboard data available.</p>';
        return;
    }

    echo '<h2>Leaderboard</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Rank</th><th>Member</th><th>Points</th></tr></thead>';
    echo '<tbody>';

    $rank = 1;
    foreach ($top_performers as $performer) {
        $user_info = get_userdata($performer->user_id);
        $display_name = $user_info ? esc_html($user_info->display_name) : 'Deleted User';

        echo "<tr>";
        echo "<td>{$rank}</td>";
        echo "<td>{$display_name}</td>";
        echo "<td>{$performer->points}</td>";
        echo "</tr>";

        $rank++;
    }

    echo '</tbody></table>';
}

// Retrieve top performers
function mkwa_get_top_performers($limit = 10) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    return $wpdb->get_results(
        $wpdb->prepare(
            "SELECT user_id, points FROM $table_name ORDER BY points DESC LIMIT %d",
            $limit
        )
    );
}

// Update leaderboard data (e.g., on points update)
function mkwa_update_leaderboard($user_id, $points) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    $wpdb->replace($table_name, [
        'user_id' => intval($user_id),
        'points' => intval($points),
        'last_updated' => current_time('mysql'),
    ]);
}

// Reset the leaderboard (e.g., monthly or quarterly)
function mkwa_reset_leaderboard() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    $wpdb->query("TRUNCATE TABLE $table_name");
}
?>
