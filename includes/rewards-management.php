<?php
function mkwa_manage_rewards() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_rewards';

    // Handle reward form submissions
    if ($_POST['action'] === 'add_reward') {
        $wpdb->insert($table_name, [
            'reward_name' => sanitize_text_field($_POST['reward_name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'points_required' => intval($_POST['points_required']),
            'stock' => intval($_POST['stock']),
            'category' => sanitize_text_field($_POST['category']),
            'seasonal' => isset($_POST['seasonal']) ? 1 : 0,
        ]);
        echo '<div class="notice notice-success"><p>Reward added successfully!</p></div>';
    } elseif ($_GET['action'] === 'delete' && isset($_GET['id'])) {
        $wpdb->delete($table_name, ['id' => intval($_GET['id'])]);
        echo '<div class="notice notice-success"><p>Reward deleted successfully!</p></div>';
    }

    // Fetch rewards
    $rewards = $wpdb->get_results("SELECT * FROM $table_name");

    // Display rewards and management form
    echo '<div class="wrap"><h1>Manage Rewards</h1>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_reward">';
    echo '<table class="form-table">';
    echo '<tr><th><label for="reward_name">Reward Name</label></th><td><input type="text" name="reward_name" required></td></tr>';
    echo '<tr><th><label for="description">Description</label></th><td><textarea name="description" required></textarea></td></tr>';
    echo '<tr><th><label for="points_required">Points Required</label></th><td><input type="number" name="points_required" required></td></tr>';
    echo '<tr><th><label for="stock">Stock</label></th><td><input type="number" name="stock" required></td></tr>';
    echo '<tr><th><label for="category">Category</label></th><td><select name="category">';
    echo '<option value="low">Low</option>';
    echo '<option value="mid">Mid</option>';
    echo '<option value="high">High</option>';
    echo '</select></td></tr>';
    echo '<tr><th><label for="seasonal">Seasonal</label></th><td><input type="checkbox" name="seasonal"></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" value="Add Reward" class="button button-primary"></p>';
    echo '</form>';

    echo '<h2>Existing Rewards</h2>';
    if ($rewards) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Points</th><th>Stock</th><th>Category</th><th>Seasonal</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($rewards as $reward) {
            echo '<tr>';
            echo "<td>{$reward->id}</td>";
            echo "<td>{$reward->reward_name}</td>";
            echo "<td>{$reward->description}</td>";
            echo "<td>{$reward->points_required}</td>";
            echo "<td>{$reward->stock}</td>";
            echo "<td>{$reward->category}</td>";
            echo "<td>" . ($reward->seasonal ? 'Yes' : 'No') . "</td>";
            echo "<td><a href='?page=mkwa-rewards&action=delete&id={$reward->id}' class='button'>Delete</a></td>";
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No rewards found.</p>';
    }
    echo '</div>';
}
