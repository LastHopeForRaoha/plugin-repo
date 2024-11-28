<?php
function mkwa_manage_challenges() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_challenges';

    // Handle form submissions
    if ($_POST['action'] == 'add_challenge') {
        $wpdb->insert($table_name, [
            'name' => sanitize_text_field($_POST['name']),
            'description' => sanitize_textarea_field($_POST['description']),
            'points' => intval($_POST['points']),
            'start_date' => sanitize_text_field($_POST['start_date']),
            'end_date' => sanitize_text_field($_POST['end_date']),
        ]);
        echo '<div class="notice notice-success"><p>Challenge added successfully!</p></div>';
    } elseif ($_GET['action'] == 'delete' && isset($_GET['id'])) {
        $wpdb->delete($table_name, ['id' => intval($_GET['id'])]);
        echo '<div class="notice notice-success"><p>Challenge deleted successfully!</p></div>';
    }

    // Fetch challenges
    $challenges = $wpdb->get_results("SELECT * FROM $table_name");

    // Display challenges and form
    echo '<div class="wrap"><h1>Manage Challenges</h1>';
    echo '<form method="post">';
    echo '<input type="hidden" name="action" value="add_challenge">';
    echo '<table class="form-table">';
    echo '<tr><th><label for="name">Name</label></th><td><input type="text" name="name" required></td></tr>';
    echo '<tr><th><label for="description">Description</label></th><td><textarea name="description" required></textarea></td></tr>';
    echo '<tr><th><label for="points">Points</label></th><td><input type="number" name="points" required></td></tr>';
    echo '<tr><th><label for="start_date">Start Date</label></th><td><input type="date" name="start_date" required></td></tr>';
    echo '<tr><th><label for="end_date">End Date</label></th><td><input type="date" name="end_date" required></td></tr>';
    echo '</table>';
    echo '<p><input type="submit" value="Add Challenge" class="button button-primary"></p>';
    echo '</form>';

    echo '<h2>Existing Challenges</h2>';
    if ($challenges) {
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>ID</th><th>Name</th><th>Description</th><th>Points</th><th>Start Date</th><th>End Date</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        foreach ($challenges as $challenge) {
            echo '<tr>';
            echo "<td>{$challenge->id}</td>";
            echo "<td>{$challenge->name}</td>";
            echo "<td>{$challenge->description}</td>";
            echo "<td>{$challenge->points}</td>";
            echo "<td>{$challenge->start_date}</td>";
            echo "<td>{$challenge->end_date}</td>";
            echo "<td><a href='?page=mkwa-challenges&action=delete&id={$challenge->id}' class='button'>Delete</a></td>";
            echo '</tr>';
        }
        echo '</tbody></table>';
    } else {
        echo '<p>No challenges found.</p>';
    }
    echo '</div>';
}
?>
