<?php
$user_id = get_current_user_id();
if (!$user_id) {
    echo '<p>Please log in to access your dashboard.</p>';
    return;
}

// Retrieve profile data
$profile_data = mkwa_get_profile_data($user_id);
?>

<div class="mkwa-dashboard">
    <h2>Welcome, <?php echo esc_html(wp_get_current_user()->display_name); ?>!</h2>

    <div class="mkwa-profile-section">
        <h3>Your Profile</h3>
        <p><strong>Bio:</strong> <?php echo esc_html($profile_data['bio'] ?: 'No bio available.'); ?></p>
        <p><strong>Leaderboard Status:</strong> <?php echo $profile_data['opt_out_leaderboard'] ? 'Opted Out' : 'Opted In'; ?></p>
        <p><strong>Social Media Shoutouts:</strong> <?php echo $profile_data['opt_out_social'] ? 'Opted Out' : 'Opted In'; ?></p>
    </div>

    <div class="mkwa-progress-section">
        <h3>Your Progress</h3>
        <p><strong>Points:</strong> <?php echo intval($profile_data['points']); ?></p>
        <p><strong>Level:</strong> <?php echo esc_html($profile_data['level']['title']); ?> (<?php echo esc_html($profile_data['level']['perk']); ?>)</p>
    </div>

    <div class="mkwa-badges-section">
        <h3>Your Badges</h3>
        <?php if (!empty($profile_data['badges'])): ?>
            <ul>
                <?php foreach ($profile_data['badges'] as $badge): ?>
                    <li><?php echo esc_html($badge); ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You haven’t earned any badges yet. Keep working toward your goals!</p>
        <?php endif; ?>
    </div>

    <div class="mkwa-actions-section">
        <h3>What’s Next?</h3>
        <a href="<?php echo esc_url(site_url('/rewards-store')); ?>" class="button">Redeem Rewards</a>
        <a href="<?php echo esc_url(site_url('/leaderboard')); ?>" class="button">View Leaderboard</a>
    </div>
</div>
