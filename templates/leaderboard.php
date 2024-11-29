<div class="mkwa-leaderboard">
    <h2>Leaderboard</h2>
    <ol>
        <?php foreach ($leaderboard_data as $user_id => $points) : ?>
            <?php 
            $user = get_userdata($user_id); 
            if (get_user_meta($user_id, 'mkwa_leaderboard_optout', true)) {
                continue; // Skip users who opted out.
            }
            ?>
            <li>
                <strong><?php echo esc_html($user->display_name); ?></strong> - 
                <?php echo esc_html($points); ?> Points
            </li>
        <?php endforeach; ?>
    </ol>
</div>
