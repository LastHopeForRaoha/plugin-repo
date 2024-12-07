<?php
// Add custom fields to user profiles
function mkwa_add_profile_fields($user) {
    ?>
    <h2>MKWA Fitness Profile</h2>
    <table class="form-table">
        <tr>
            <th><label for="mkwa_bio">Bio</label></th>
            <td>
                <textarea name="mkwa_bio" id="mkwa_bio" rows="5" cols="30"><?php echo esc_attr(get_user_meta($user->ID, 'mkwa_bio', true)); ?></textarea>
                <p class="description">Write a short bio about yourself.</p>
            </td>
        </tr>
        <tr>
            <th><label for="mkwa_opt_out_leaderboard">Leaderboard Opt-Out</label></th>
            <td>
                <input type="checkbox" name="mkwa_opt_out_leaderboard" id="mkwa_opt_out_leaderboard" value="1" <?php checked(get_user_meta($user->ID, 'mkwa_opt_out_leaderboard', true), 1); ?> />
                <p class="description">Check this box if you do not want to appear on the leaderboard.</p>
            </td>
        </tr>
        <tr>
            <th><label for="mkwa_social_opt_out">Social Media Opt-Out</label></th>
            <td>
                <input type="checkbox" name="mkwa_social_opt_out" id="mkwa_social_opt_out" value="1" <?php checked(get_user_meta($user->ID, 'mkwa_social_opt_out', true), 1); ?> />
                <p class="description">Check this box if you do not want to be featured in social media shoutouts.</p>
            </td>
        </tr>
        <tr>
            <th><label for="mkwa_awarded_badges">Awarded Badges</label></th>
            <td>
                <textarea name="mkwa_awarded_badges" id="mkwa_awarded_badges" rows="3" cols="30" readonly><?php echo esc_attr(implode(', ', mkwa_get_badges($user->ID))); ?></textarea>
                <p class="description">These are the badges you’ve earned so far!</p>
            </td>
        </tr>
        <tr>
            <th><label for="mkwa_points">Total Points</label></th>
            <td>
                <input type="text" name="mkwa_points" id="mkwa_points" value="<?php echo esc_attr(mkwa_get_points($user->ID)); ?>" readonly />
                <p class="description">This is your current points total.</p>
            </td>
        </tr>
        <tr>
            <th><label for="mkwa_current_level">Current Level</label></th>
            <td>
                <input type="text" name="mkwa_current_level" id="mkwa_current_level" value="<?php echo esc_attr(mkwa_get_user_level($user->ID)['title']); ?>" readonly />
                <p class="description">Your current MKWA Fitness level.</p>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'mkwa_add_profile_fields');
add_action('edit_user_profile', 'mkwa_add_profile_fields');

// Save custom fields to user profiles
function mkwa_save_profile_fields($user_id) {
    if (current_user_can('edit_user', $user_id)) {
        update_user_meta($user_id, 'mkwa_bio', sanitize_textarea_field($_POST['mkwa_bio']));
        update_user_meta($user_id, 'mkwa_opt_out_leaderboard', isset($_POST['mkwa_opt_out_leaderboard']) ? 1 : 0);
        update_user_meta($user_id, 'mkwa_social_opt_out', isset($_POST['mkwa_social_opt_out']) ? 1 : 0);
    }
}
add_action('personal_options_update', 'mkwa_save_profile_fields');
add_action('edit_user_profile_update', 'mkwa_save_profile_fields');

// Retrieve profile data
function mkwa_get_profile_data($user_id) {
    return [
        'bio' => get_user_meta($user_id, 'mkwa_bio', true),
        'points' => mkwa_get_points($user_id),
        'badges' => mkwa_get_badges($user_id),
        'level' => mkwa_get_user_level($user_id),
        'opt_out_leaderboard' => get_user_meta($user_id, 'mkwa_opt_out_leaderboard', true),
        'opt_out_social' => get_user_meta($user_id, 'mkwa_social_opt_out', true),
    ];
}

// Shortcode for the member dashboard
function mkwa_member_dashboard_shortcode() {
    ob_start();
    include MKWA_PLUGIN_PATH . 'templates/member-dashboard.php';
    return ob_get_clean();
}
add_shortcode('mkwa_dashboard', 'mkwa_member_dashboard_shortcode');

// Function to fetch user points
function mkwa_get_points($user_id) {
    return MKWAPointsSystem::get_user_points($user_id);
}

// Function to fetch user badges
function mkwa_get_badges($user_id) {
    return MKWABadgesSystem::get_user_badges($user_id);
}
?>
