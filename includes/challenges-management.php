<?php
function mkwa_manage_challenges() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'mkwa_challenges';

    // Handle form submissions
    if (filter_input(INPUT_POST, 'action') === 'add_challenge') {
        $name = sanitize_text_field(filter_input(INPUT_POST, 'name'));
        $description = sanitize_textarea_field(filter_input(INPUT_POST, 'description'));
        $points = intval(filter_input(INPUT_POST, 'points'));
        $start_date = sanitize_text_field(filter_input(INPUT_POST, 'start_date'));
        $end_date = sanitize_text_field(filter_input(INPUT_POST, 'end_date'));

        $wpdb->insert($table_name, compact('name', 'description', 'points', 'start_date', 'end_date'), ['%s', '%s', '%d', '%s', '%s']);
        echo '<div class="notice notice-success"><p>' . __('Challenge added successfully!', 'mkwa-fitness') . '</p></div>';
    } elseif (filter_input(INPUT_GET, 'action') === 'delete' && filter_input(INPUT_GET, 'id')) {
        $wpdb->delete($table_name, ['id' => intval(filter_input(INPUT_GET, 'id'))]);
        echo '<div class="notice notice-success"><p>' . __('Challenge deleted successfully!', 'mkwa-fitness') . '</p></div>';
    }

    // Fetch challenges
    $challenges = $wpdb->get_results("SELECT * FROM $table_name");

    // Display challenges and form
    ?>
    <div class="wrap">
        <h1><?php _e('Manage Challenges', 'mkwa-fitness'); ?></h1>
        <form method="post">
            <input type="hidden" name="action" value="add_challenge">
            <table class="form-table">
                <tr><th><label for="name"><?php _e('Name', 'mkwa-fitness'); ?></label></th><td><input type="text" name="name" required></td></tr>
                <tr><th><label for="description"><?php _e('Description', 'mkwa-fitness'); ?></label></th><td><textarea name="description" required></textarea></td></tr>
                <tr><th><label for="points"><?php _e('Points', 'mkwa-fitness'); ?></label></th><td><input type="number" name="points" required></td></tr>
                <tr><th><label for="start_date"><?php _e('Start Date', 'mkwa-fitness'); ?></label></th><td><input type="date" name="start_date" required></td></tr>
                <tr><th><label for="end_date"><?php _e('End Date', 'mkwa-fitness'); ?></label></th><td><input type="date" name="end_date" required></td></tr>
            </table>
            <p><input type="submit" value="<?php _e('Add Challenge', 'mkwa-fitness'); ?>" class="button button-primary"></p>
        </form>

        <h2><?php _e('Existing Challenges', 'mkwa-fitness'); ?></h2>
        <?php if ($challenges): ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr><th>ID</th><th><?php _e('Name', 'mkwa-fitness'); ?></th><th><?php _e('Description', 'mkwa-fitness'); ?></th><th><?php _e('Points', 'mkwa-fitness'); ?></th><th><?php _e('Start Date', 'mkwa-fitness'); ?></th><th><?php _e('End Date', 'mkwa-fitness'); ?></th><th><?php _e('Actions', 'mkwa-fitness'); ?></th></tr>
                </thead>
                <tbody>
                    <?php foreach ($challenges as $challenge): ?>
                        <tr>
                            <td><?php echo esc_html($challenge->id); ?></td>
                            <td><?php echo esc_html($challenge->name); ?></td>
                            <td><?php echo esc_html($challenge->description); ?></td>
                            <td><?php echo esc_html($challenge->points); ?></td>
                            <td><?php echo esc_html($challenge->start_date); ?></td>
                            <td><?php echo esc_html($challenge->end_date); ?></td>
                            <td><a href="?page=mkwa-challenges&action=delete&id=<?php echo intval($challenge->id); ?>" class="button"><?php _e('Delete', 'mkwa-fitness'); ?></a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php _e('No challenges found.', 'mkwa-fitness'); ?></p>
        <?php endif; ?>
    </div>
    <?php
}
?>
