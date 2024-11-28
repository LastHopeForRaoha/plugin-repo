<?php
$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p>Please log in to access the Rewards Store.</p>';
    return;
}

$rewards = mkwa_get_rewards();
$user_points = mkwa_get_points($user_id);

if (empty($rewards)) {
    echo '<p>No rewards are available at the moment. Check back later!</p>';
    return;
}

echo '<h2>Rewards Store</h2>';
echo "<p>Your Points: <strong>{$user_points}</strong></p>";

echo '<ul>';
foreach ($rewards as $reward) {
    $disabled = $user_points < $reward->points_required ? 'disabled' : '';
    echo "<li>";
    echo "<strong>{$reward->name}</strong>: {$reward->description} - {$reward->points_required} points";
    echo "<form method='POST' action=''>";
    echo "<input type='hidden' name='reward_id' value='{$reward->id}'>";
    echo "<button type='submit' {$disabled}>Redeem</button>";
    echo "</form>";
    echo "</li>";
}
echo '</ul>';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reward_id'])) {
    $reward_id = intval($_POST['reward_id']);
    $result = mkwa_redeem_reward($user_id, $reward_id);

    if ($result['success']) {
        echo "<p style='color: green;'>{$result['message']}</p>";
    } else {
        echo "<p style='color: red;'>{$result['message']}</p>";
    }
}
?>
