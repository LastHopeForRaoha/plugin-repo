<?php
/**
 * MKWA Fitness - Leaderboard
 * Handles functionality for the Leaderboard.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MKWA_Leaderboard {
    public function __construct() {
        // Register shortcode.
        add_shortcode('mkwa_leaderboard', [$this, 'render_leaderboard']);
    }

    /**
     * Render the Leaderboard.
     */
    public function render_leaderboard() {
        // Fetch leaderboard data from the database.
        $leaderboard_data = get_option('mkwa_fitness_leaderboard', []);

        if (empty($leaderboard_data)) {
            return '<p>The leaderboard is currently empty. Check back later!</p>';
        }

        // Sort leaderboard by points in descending order.
        arsort($leaderboard_data);

        ob_start();
        ?>
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
        <?php
        return ob_get_clean();
    }

    /**
     * Update Leaderboard Data.
     * Can be triggered by a cron job or manually by admin.
     */
    public static function update_leaderboard() {
        $users = get_users(['fields' => ['ID']]);
        $leaderboard = [];

        foreach ($users as $user) {
            $points = get_user_meta($user->ID, 'mkwa_points', true) ?: 0;
            $leaderboard[$user->ID] = $points;
        }

        update_option('mkwa_fitness_leaderboard', $leaderboard);
    }
}

// Initialize the class.
new MKWA_Leaderboard();
