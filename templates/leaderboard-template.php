<?php
// Get top performers
$top_performers = mkwa_get_top_performers(10);

if (empty($top_performers)) {
    echo '<p>No leaderboard data available.</p>';
    return;
}

echo '<h2>Top Performers</h2>';
echo '<table class="leaderboard-table">';
echo '<thead><tr><th>Rank</th><th>Member</th><th>Points</th></tr></thead>';
echo '<tbody>';

$rank = 1;
foreach ($top_performers as $performer) {
    $user_info = get_userdata($performer->user_id);
    $display_name = $user_info ? esc_html($user_info->display_name) : 'Deleted User';
    $points = intval($performer->points);

    echo '<tr>';
    echo "<td>{$rank}</td>";
    echo "<td>{$display_name}</td>";
    echo "<td>{$points}</td>";
    echo '</tr>";
    $rank++;
}

echo '</tbody></table>';
?>
