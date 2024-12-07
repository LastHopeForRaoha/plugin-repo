<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class MKWARegistration {
    public static function init() {
        // Register the shortcode for the registration form
        add_shortcode('mkwa_registration_form', [__CLASS__, 'display_registration_form']);
        // AJAX handler for user registration
        add_action('wp_ajax_nopriv_mkwa_register_user', [__CLASS__, 'register_user']);
    }

    /**
     * Display the custom registration form.
     */
    public static function display_registration_form() {
        if (is_user_logged_in()) {
            return '<p>You are already registered and logged in.</p>';
        }

        ob_start();
        ?>
        <form id="mkwa-registration" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post" enctype="multipart/form-data">
            <p>
                <label for="username">Username</label>
                <input type="text" name="username" id="username" required>
            </p>
            <p>
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required>
            </p>
            <p>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required>
            </p>
            <p>
                <label for="bio">Bio</label>
                <textarea name="bio" id="bio" rows="4"></textarea>
            </p>
            <p>
                <label for="profile_picture">Profile Picture (optional)</label>
                <input type="file" name="profile_picture" id="profile_picture" accept="image/*">
            </p>
            <p>
                <button type="submit">Register</button>
            </p>
            <input type="hidden" name="action" value="mkwa_register_user">
            <?php wp_nonce_field('mkwa_register_user', 'mkwa_nonce'); ?>
        </form>
        <div id="mkwa-registration-response"></div>
        <?php
        return ob_get_clean();
    }

    /**
     * Handle user registration via AJAX.
     */
    public static function register_user() {
        // Verify nonce
        if (!isset($_POST['mkwa_nonce']) || !wp_verify_nonce($_POST['mkwa_nonce'], 'mkwa_register_user')) {
            wp_send_json_error('Invalid request.');
        }

        // Sanitize input data
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        $bio = sanitize_textarea_field($_POST['bio']);
        $profile_picture = $_FILES['profile_picture'];

        // Validate required fields
        if (empty($username) || empty($email) || empty($password)) {
            wp_send_json_error('Please fill in all required fields.');
        }

        // Check if username or email already exists
        if (username_exists($username) || email_exists($email)) {
            wp_send_json_error('Username or email already exists.');
        }

        // Create the user
        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            wp_send_json_error($user_id->get_error_message());
        }

        // Add bio to user meta
        update_user_meta($user_id, 'mkwa_bio', $bio);

        // Handle profile picture upload
        if ($profile_picture && !empty($profile_picture['tmp_name'])) {
            $upload = wp_handle_upload($profile_picture, ['test_form' => false]);
            if (isset($upload['url'])) {
                update_user_meta($user_id, 'profile_picture', $upload['url']);
            }
        }

        // Initialize gamification features
        MKWAPointsSystem::add_points($user_id, 0, 'Welcome to MKWA Fitness');
        MKWABadgesSystem::assign_badge($user_id, 'welcome_badge', 'Welcome to the MKWA community!');

        // Send success response
        wp_send_json_success('Registration successful! You can now log in.');
    }
}

// Initialize the MKWARegistration class
MKWARegistration::init();
