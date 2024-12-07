<?php
/**
 * MKWA Fitness - Admin Menu
 * Handles the admin menu and backend functionality.
 */

// Ensure the script is not accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Register the MKWA Fitness Admin Menu and Submenus
 */
function mkwa_admin_menu() {
    // Main Menu Page
    add_menu_page(
        'MKWA Fitness',                 // Page Title
        'MKWA Fitness',                 // Menu Title
        'manage_options',               // Capability
        'mkwa-fitness',                 // Menu Slug
        'mkwa_admin_dashboard',         // Callback Function
        'dashicons-awards',             // Icon
        25                              // Position
    );

    // Add Submenu Pages
    add_submenu_page(
        'mkwa-fitness',
        'Manage Challenges',
        'Challenges',
        'manage_options',
        'mkwa-challenges',
        'mkwa_manage_challenges'
    );

    add_submenu_page(
        'mkwa-fitness',
        'Manage Rewards',
        'Rewards',
        'manage_options',
        'mkwa-rewards',
        'mkwa_manage_rewards'
    );

    add_submenu_page(
        'mkwa-fitness',
        'Manage Leaderboards',
        'Leaderboards',
        'manage_options',
        'mkwa-leaderboards',
        'mkwa_manage_leaderboards'
    );

    add_submenu_page(
        'mkwa-fitness',
        'Badges Management',
        'Badges',
        'manage_options',
        'mkwa-badges',
        'mkwa_manage_badges'
    );
}
add_action('admin_menu', 'mkwa_admin_menu');

/**
 * Admin Dashboard Page
 */
function mkwa_admin_dashboard() {
    echo '<div class="wrap"><h1>MKWA Fitness Admin Dashboard</h1>';
    echo '<p>Welcome to the MKWA Fitness management system. Use the menu on the left to manage challenges, rewards, leaderboards, and badges.</p>';
    echo '<p>Key Gamification Insights:</p>';
    echo '<ul>
        <li><strong>Total Active Members:</strong> ' . mkwa_get_total_active_members() . '</li>
        <li><strong>Total Points Earned This Month:</strong> ' . mkwa_get_total_points_this_month() . '</li>
        <li><strong>Top Leaderboard Member:</strong> ' . mkwa_get_top_leaderboard_member() . '</li>
    </ul>';
    echo '</div>';
}

/**
 * Challenges Management Page
 */
function mkwa_manage_challenges() {
    echo '<div class="wrap"><h1>Manage Challenges</h1>';
    echo '<p>Create and manage challenges for your members, track progress, and assign rewards for participation and completion.</p>';
    echo '<p>Coming soon: Integration with seasonal and team-based challenges as outlined in the gamification system.</p>';
}

/**
 * Rewards Management Page
 */
function mkwa_manage_rewards() {
    echo '<div class="wrap"><h1>Manage Rewards</h1>';
    echo '<p>Manage the redemption store, add new rewards, and review member redemption history.</p>';
    echo '<form method="POST">
            <input type="text" name="reward_name" placeholder="Reward Name" required>
            <input type="number" name="reward_points" placeholder="Points Required" required>
            <button type="submit" name="new_reward">Add Reward</button>
          </form>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_reward'])) {
        $reward = [
            'id' => uniqid(),
            'name' => sanitize_text_field($_POST['reward_name']),
            'points' => intval($_POST['reward_points']),
        ];
        $rewards = get_option('mkwa_rewards', []);
        $rewards[] = $reward;
        update_option('mkwa_rewards', $rewards);
        echo '<p>Reward added successfully!</p>';
    }

    echo '<h3>Current Rewards</h3><ul>';
    $rewards = get_option('mkwa_rewards', []);
    foreach ($rewards as $reward) {
        echo '<li>' . esc_html($reward['name']) . ' - ' . esc_html($reward['points']) . ' Points</li>';
    }
    echo '</ul></div>';
}

/**
 * Leaderboards Management Page
 */
function mkwa_manage_leaderboards() {
    echo '<div class="wrap"><h1>Manage Leaderboards</h1>';
    echo '<p>Review leaderboard standings, reset scores, and assign recognition for top performers.</p>';
    echo '<form method="POST">
            <button type="submit" name="reset_leaderboards">Reset Leaderboards</button>
          </form>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_leaderboards'])) {
        MKWALeaderboard::reset_leaderboards();
        echo '<p>Leaderboards reset successfully!</p>';
    }
}

/**
 * Badges Management Page
 */
function mkwa_manage_badges() {
    echo '<div class="wrap"><h1>Manage Badges</h1>';
    echo '<p>Assign badges to members, manage badge types, and review badge statistics.</p>';
    echo '<form method="POST">
            <label for="user_id">User ID:</label>
            <input type="number" id="user_id" name="user_id" required>
            <label for="badge_name">Badge Name:</label>
            <input type="text" id="badge_name" name="badge_name" required>
            <button type="submit" name="assign_badge">Assign Badge</button>
          </form>';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_badge'])) {
        $user_id = intval($_POST['user_id']);
        $badge_name = sanitize_text_field($_POST['badge_name']);
        MKWABadgesSystem::assign_badge($user_id, $badge_name);
        echo '<p>Badge assigned successfully to User ID ' . esc_html($user_id) . '</p>';
    }
}

/**
 * Helper Functions
 */
function mkwa_get_total_active_members() {
    return 120; // Replace with actual logic to fetch active members
}

function mkwa_get_total_points_this_month() {
    return 15000; // Replace with actual points calculation logic
}

function mkwa_get_top_leaderboard_member() {
    return 'John Doe'; // Replace with actual logic to fetch the top member
}
