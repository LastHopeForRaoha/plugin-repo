<?php
/**
 * MKWA Fitness - Admin
 * Handles the admin menu and backend functionality.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MKWA_Admin {
    public function __construct() {
        // Register admin menu.
        add_action('admin_menu', [$this, 'register_admin_menu']);
    }

    /**
     * Register the admin menu.
     */
    public function register_admin_menu() {
        add_menu_page(
            'MKWA Fitness',
            'MKWA Fitness',
            'manage_options',
            'mkwa-fitness',
            [$this, 'admin_dashboard_page'],
            'dashicons-admin-site',
            20
        );

        add_submenu_page(
            'mkwa-fitness',
            'Manage Rewards',
            'Rewards',
            'manage_options',
            'mkwa-rewards',
            [$this, 'manage_rewards_page']
        );

        add_submenu_page(
            'mkwa-fitness',
            'Manage Challenges',
            'Challenges',
            'manage_options',
            'mkwa-challenges',
            [$this, 'manage_challenges_page']
        );

        add_submenu_page(
            'mkwa-fitness',
            'Manage Leaderboard',
            'Leaderboard',
            'manage_options',
            'mkwa-leaderboard',
            [$this, 'manage_leaderboard_page']
        );
    }

    /**
     * Admin Dashboard Page.
     */
    public function admin_dashboard_page() {
        echo '<h1>Welcome to MKWA Fitness Admin Dashboard</h1>';
    }

    /**
     * Manage Rewards Page.
     */
    public function manage_rewards_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_reward'])) {
            $new_reward = [
                'id' => uniqid(),
                'name' => sanitize_text_field($_POST['reward_name']),
                'points' => intval($_POST['reward_points']),
            ];

            $rewards = get_option('mkwa_fitness_rewards', []);
            $rewards[] = $new_reward;
            update_option('mkwa_fitness_rewards', $rewards);

            echo '<p>Reward added successfully!</p>';
        }

        $rewards = get_option('mkwa_fitness_rewards', []);
        ?>
        <h2>Manage Rewards</h2>
        <form method="POST">
            <input type="text" name="reward_name" placeholder="Reward Name" required>
            <input type="number" name="reward_points" placeholder="Points Required" required>
            <button type="submit" name="new_reward">Add Reward</button>
        </form>
        <ul>
            <h3>Existing Rewards</h3>
            <?php foreach ($rewards as $reward) : ?>
                <li>
                    <strong><?php echo esc_html($reward['name']); ?></strong> - 
                    <?php echo esc_html($reward['points']); ?> Points
                </li>
            <?php endforeach; ?>
        </ul>
        <?php
    }

    /**
     * Manage Challenges Page (Placeholder).
     */
    public function manage_challenges_page() {
        echo '<h2>Manage Challenges</h2>';
        echo '<p>Challenge management coming soon!</p>';
    }

    /**
     * Manage Leaderboard Page.
     */
    public function manage_leaderboard_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_leaderboard'])) {
            MKWA_Leaderboard::update_leaderboard();
            echo '<p>Leaderboard updated successfully!</p>';
        }

        ?>
        <h2>Manage Leaderboard</h2>
        <form method="POST">
            <button type="submit" name="update_leaderboard">Update Leaderboard</button>
        </form>
        <?php
    }
}

// Initialize the class.
new MKWA_Admin();
