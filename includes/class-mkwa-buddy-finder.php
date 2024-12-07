<?php
if (!defined('ABSPATH')) {
    exit;
}

class MKWABuddyFinder {
    private static $table_name;

    public static function init() {
        global $wpdb;
        self::$table_name = $wpdb->prefix . 'mkwa_buddy_finder';

        // Register shortcode for buddy finder
        add_shortcode('mkwa_buddy_finder', [__CLASS__, 'display_buddy_finder']);

        // Schedule cleanup for inactive buddy finder entries
        if (!wp_next_scheduled('mkwa_clean_buddy_finder')) {
            wp_schedule_event(time(), 'daily', 'mkwa_clean_buddy_finder');
        }

        add_action('mkwa_clean_buddy_finder', [__CLASS__, 'cleanup_inactive_entries']);
    }

    /**
     * Create the buddy finder table.
     */
    public static function create_table() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS " . self::$table_name . " (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            availability TEXT NOT NULL,
            fitness_goals TEXT NOT NULL,
            opt_in TINYINT(1) DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    /**
     * Display the buddy finder interface.
     */
    public static function display_buddy_finder() {
        $user_id = get_current_user_id();
        if (!$user_id) {
            return '<p>' . esc_html__('Please log in to access the Buddy Finder.', 'mkwafitness') . '</p>';
        }

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['buddy_finder_submit'])) {
            check_admin_referer('mkwa_buddy_finder', 'mkwa_buddy_finder_nonce');

            $availability = isset($_POST['availability']) ? array_map('sanitize_text_field', $_POST['availability']) : [];
            $fitness_goals = isset($_POST['fitness_goals']) ? array_map('sanitize_text_field', $_POST['fitness_goals']) : [];
            $opt_in = isset($_POST['opt_in']) ? 1 : 0;

            self::update_preferences($user_id, $availability, $fitness_goals, $opt_in);

            echo '<p>' . esc_html__('Preferences updated successfully!', 'mkwafitness') . '</p>';
        }

        // Fetch user preferences
        $prefs = self::get_user_preferences($user_id);
        $availability = $prefs->availability ?? [];
        $fitness_goals = $prefs->fitness_goals ?? [];
        $opt_in = $prefs->opt_in ?? 0;

        // Fetch buddies
        $buddies = self::find_buddies($user_id);

        ob_start();
        ?>
        <div class="mkwa-buddy-finder">
            <h2><?php esc_html_e('Buddy Finder', 'mkwafitness'); ?></h2>
            <form method="POST">
                <?php wp_nonce_field('mkwa_buddy_finder', 'mkwa_buddy_finder_nonce'); ?>
                <label for="availability"><?php esc_html_e('Availability:', 'mkwafitness'); ?></label><br>
                <input type="checkbox" name="availability[]" value="morning" <?php checked(in_array('morning', $availability)); ?>> <?php esc_html_e('Morning', 'mkwafitness'); ?><br>
                <input type="checkbox" name="availability[]" value="afternoon" <?php checked(in_array('afternoon', $availability)); ?>> <?php esc_html_e('Afternoon', 'mkwafitness'); ?><br>
                <input type="checkbox" name="availability[]" value="evening" <?php checked(in_array('evening', $availability)); ?>> <?php esc_html_e('Evening', 'mkwafitness'); ?><br>

                <label for="fitness_goals"><?php esc_html_e('Fitness Goals:', 'mkwafitness'); ?></label><br>
                <input type="checkbox" name="fitness_goals[]" value="strength" <?php checked(in_array('strength', $fitness_goals)); ?>> <?php esc_html_e('Strength Training', 'mkwafitness'); ?><br>
                <input type="checkbox" name="fitness_goals[]" value="cardio" <?php checked(in_array('cardio', $fitness_goals)); ?>> <?php esc_html_e('Cardio', 'mkwafitness'); ?><br>
                <input type="checkbox" name="fitness_goals[]" value="flexibility" <?php checked(in_array('flexibility', $fitness_goals)); ?>> <?php esc_html_e('Flexibility', 'mkwafitness'); ?><br>

                <label for="opt_in"><?php esc_html_e('Opt into Buddy Finder:', 'mkwafitness'); ?></label>
                <input type="checkbox" name="opt_in" value="1" <?php checked($opt_in); ?>><br><br>

                <button type="submit" name="buddy_finder_submit"><?php esc_html_e('Update Preferences', 'mkwafitness'); ?></button>
            </form>

            <h3><?php esc_html_e('Potential Buddies', 'mkwafitness'); ?></h3>
            <?php if (!empty($buddies)) : ?>
                <ul>
                    <?php foreach ($buddies as $buddy) : ?>
                        <li>
                            <strong><?php echo esc_html(get_userdata($buddy->user_id)->display_name ?? __('Anonymous', 'mkwafitness')); ?></strong><br>
                            <?php esc_html_e('Availability:', 'mkwafitness'); ?> <?php echo esc_html(implode(', ', $buddy->availability)); ?><br>
                            <?php esc_html_e('Goals:', 'mkwafitness'); ?> <?php echo esc_html(implode(', ', $buddy->fitness_goals)); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e('No buddies found. Update your preferences or check back later!', 'mkwafitness'); ?></p>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Update user preferences for buddy finder.
     */
    public static function update_preferences($user_id, $availability, $fitness_goals, $opt_in) {
        global $wpdb;

        $data = [
            'availability' => maybe_serialize($availability),
            'fitness_goals' => maybe_serialize($fitness_goals),
            'opt_in' => $opt_in ? 1 : 0,
        ];

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM " . self::$table_name . " WHERE user_id = %d",
            $user_id
        ));

        if ($existing) {
            $wpdb->update(
                self::$table_name,
                $data,
                ['user_id' => $user_id],
                ['%s', '%s', '%d'],
                ['%d']
            );
        } else {
            $data['user_id'] = $user_id;
            $wpdb->insert(
                self::$table_name,
                $data,
                ['%d', '%s', '%s', '%d']
            );
        }

        // Award points for updating preferences
        MKWAPointsSystem::add_points($user_id, 10, __('Updated Buddy Finder Preferences', 'mkwafitness'));
    }

    /**
     * Cleanup inactive entries.
     */
    public static function cleanup_inactive_entries() {
        global $wpdb;

        $wpdb->query($wpdb->prepare(
            "DELETE FROM " . self::$table_name . " WHERE opt_in = 0 AND created_at < %s",
            date('Y-m-d H:i:s', strtotime('-30 days'))
        ));
    }
}

// Initialize the class
MKWABuddyFinder::init();
