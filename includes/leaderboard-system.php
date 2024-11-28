<?php
// Shortcode to display the leaderboard
function mkwa_leaderboard_shortcode() {
    ob_start();
    include MKWA_PLUGIN_PATH . 'templates/leaderboard-template.php';
    return ob_get_clean();
}
add_shortcode('mkwa_leaderboard', 'mkwa_leaderboard_shortcode');
?>

// Create leaderboard table on plugin activation
function mkwa_create_leaderboard_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        points int NOT NULL,
        last_updated datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mkwa_create_leaderboard_table');

// Update leaderboard data (called daily or on points update)
function mkwa_update_leaderboard($user_id, $points) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    $wpdb->replace($table_name, [
        'user_id' => $user_id,
        'points' => $points,
        'last_updated' => current_time('mysql'),
    ]);
}

// Retrieve top performers
function mkwa_get_top_performers($limit = 10) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_leaderboard';

    $results = $wpdb->get_results(
        "SELECT user_id, points FROM $table_name ORDER BY points DESC LIMIT $limit"
    );

    return $results;
}
?>
