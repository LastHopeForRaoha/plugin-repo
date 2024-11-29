<?php
/**
 * MKWA Fitness - Rewards Store
 * Handles functionality for the Rewards Store.
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class MKWA_Rewards {
    public function __construct() {
        // Register shortcode.
        add_shortcode('mkwa_rewards_store', [$this, 'render_rewards_store']);

        // Add AJAX handler for redeeming rewards.
        add_action('wp_ajax_mkwa_redeem_reward', [$this, 'redeem_reward']);
    }

    /**
     * Render the Rewards Store.
     */
    public function render_rewards_store() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access the rewards store.</p>';
        }

        $user_id = get_current_user_id();
        $points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;
        $rewards = get_option('mkwa_fitness_rewards', []); // Rewards stored in options.

        ob_start();
        ?>
        <div class="mkwa-rewards-store">
            <h2>Rewards Store</h2>
            <p>You have <strong><?php echo esc_html($points); ?></strong> points.</p>
            <ul>
                <?php if (!empty($rewards)) : ?>
                    <?php foreach ($rewards as $reward) : ?>
                        <li>
                            <strong><?php echo esc_html($reward['name']); ?></strong> - 
                            <?php echo esc_html($reward['points']); ?> Points
                            <?php if ($points >= $reward['points']) : ?>
                                <button class="redeem-reward" data-reward="<?php echo esc_attr($reward['id']); ?>">Redeem</button>
                            <?php else : ?>
                                <span>Not enough points</span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                <?php else : ?>
                    <li>No rewards available at the moment. Check back later!</li>
                <?php endif; ?>
            </ul>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle reward redemption via AJAX.
     */
    public function redeem_reward() {
        check_ajax_referer('mkwa_rewards_action', 'security');

        $user_id = get_current_user_id();
        $points = get_user_meta($user_id, 'mkwa_points', true) ?: 0;

        $reward_id = isset($_POST['reward_id']) ? sanitize_text_field($_POST['reward_id']) : '';
        $rewards = get_option('mkwa_fitness_rewards', []);

        $selected_reward = null;
        foreach ($rewards as $reward) {
            if ($reward['id'] === $reward_id) {
                $selected_reward = $reward;
                break;
            }
        }

        if (!$selected_reward) {
            wp_send_json_error(['message' => 'Reward not found.']);
        }

        if ($points < $selected_reward['points']) {
            wp_send_json_error(['message' => 'Not enough points to redeem this reward.']);
        }

        // Deduct points and confirm redemption.
        update_user_meta($user_id, 'mkwa_points', $points - $selected_reward['points']);
        wp_send_json_success(['message' => 'Reward redeemed successfully!']);
    }
}

// Initialize the class.
new MKWA_Rewards();
