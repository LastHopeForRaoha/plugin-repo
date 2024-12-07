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
        add_action('admin_post_mkwa_save_rewards', [$this, 'handle_reward_submission']);
        add_action('admin_post_mkwa_update_leaderboard', [$this, 'update_leaderboard']);
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

        add_submenu_page(
            'mkwa-fitness',
            'Badge System',
            'Badges',
            'manage_options',
            'mkwa-badges',
            [$this, 'manage_badges_page']
        );
    }

    /**
     * Admin Dashboard Page.
     */
    public function admin_dashboard_page() {
        echo '<h1>Welcome to MKWA Fitness Admin Dashboard</h1>';
        echo '<p>Use the menu to manage rewards, challenges, leaderboards, and badges.</p>';
    }

    /**
     * Manage Rewards Page.
     */
    public function manage_rewards_page() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_reward'])) {
            check_admin_referer('mkwa_save_rewards', '_wpnonce');

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
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mkwa_save_rewards">
            <?php wp_nonce_field('mkwa_save_rewards', '_wpnonce'); ?>
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
     * Manage Challenges Page.
     */
    public function manage_challenges_page() {
        echo '<h2>Manage Challenges</h2>';
        echo '<p>Challenge management functionality coming soon!</p>';
    }

    /**
     * Manage Leaderboard Page.
     */
    public function manage_leaderboard_page() {
        ?>
        <h2>Manage Leaderboard</h2>
        <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mkwa_update_leaderboard">
            <?php wp_nonce_field('mkwa_update_leaderboard', '_wpnonce'); ?>
            <button type="submit" name="update_leaderboard">Update Leaderboard</button>
        </form>
        <?php
    }

    /**
     * Manage Badges Page.
     */
    public function manage_badges_page() {
        ?>
        <h2>Manage Badges</h2>
        <p>Assign badges to members or update badge-related rules.</p>
        <form method="POST">
            <label for="user_id">User ID:</label>
            <input type="number" id="user_id" name="user_id" required>
            <label for="badge_slug">Badge Slug:</label>
            <input type="text" id="badge_slug" name="badge_slug" required>
            <button type="submit" name="award_badge">Award Badge</button>
        </form>
        <?php
    }

    /**
     * Handle Reward Submission.
     */
    public function handle_reward_submission() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('mkwa_save_rewards', '_wpnonce');

        $new_reward = [
            'id' => uniqid(),
            'name' => sanitize_text_field($_POST['reward_name']),
            'points' => intval($_POST['reward_points']),
        ];

        $rewards = get_option('mkwa_fitness_rewards', []);
        $rewards[] = $new_reward;
        update_option('mkwa_fitness_rewards', $rewards);

        wp_redirect(admin_url('admin.php?page=mkwa-rewards'));
        exit;
    }

    /**
     * Update Leaderboard.
     */
    public function update_leaderboard() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        check_admin_referer('mkwa_update_leaderboard', '_wpnonce');

        MKWA_Leaderboard::update_leaderboard();

        wp_redirect(admin_url('admin.php?page=mkwa-leaderboard&success=1'));
        exit;
    }
}

// Initialize the class.
new MKWA_Admin();
