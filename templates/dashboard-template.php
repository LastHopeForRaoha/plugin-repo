<?php
$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p>Please log in to view your dashboard.</p>';
    return;
}

$points = mkwa_get_points($user_id);
$badges = mkwa_get_badges($user_id);
$level = mkwa_get_user_level($user_id);
?>

<h2>Member Dashboard</h2>
<p><strong>Points:</strong> <?php echo $points; ?></p>
<p><strong>Level:</strong> <?php echo $level['title']; ?> (Perk: <?php echo $level['perk']; ?>)</p>
<p><strong>Badges:</strong></p>
<ul>
    <?php foreach ($badges as $badge) : ?>
        <li><?php echo $badge; ?></li>
    <?php endforeach; ?>
</ul>
