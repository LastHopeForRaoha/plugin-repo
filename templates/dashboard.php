<div class="dashboard-container">
    <h2>Hello, <?php echo wp_get_current_user()->display_name; ?>!</h2>
    <p>Your current points: <?php echo get_user_meta(get_current_user_id(), 'mkwa_points', true); ?></p>
    <h3>Quests:</h3>
    <?php echo do_shortcode('[mkwa_daily_quests]'); ?>
    <h3>Leaderboard Snapshot:</h3>
    <?php echo do_shortcode('[mkwa_leaderboard]'); ?>
</div>
