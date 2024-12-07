<?php
// Define the custom registration form and AJAX handler
function mkwa_custom_registration_form() {
    if (is_user_logged_in()) {
        return '<p>You are already registered and logged in.</p>';
    }

    ob_start(); ?>
    <form id="mkwaregistration" action="<?php echo esc_url(admin_url('admin-ajax.php')); ?>" method="post">
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
            <button type="submit">Register</button>
        </p>
        <input type="hidden" name="action" value="mkwa_register_user">
        <?php wp_nonce_field('mkwa_register_user', 'mkwa_nonce'); ?>
    </form>
    <div id="mkwa-registration-response"></div>
    <?php return ob_get_clean();
}
add_shortcode('mkwa_registration_form', 'mkwa_custom_registration_form');

function mkwa_register_user() {
    if (!isset($_POST['mkwa_nonce']) || !wp_verify_nonce($_POST['mkwa_nonce'], 'mkwa_register_user')) {
        wp_send_json_error('Invalid request');
    }

    $username = sanitize_text_field($_POST['username']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $bio = sanitize_textarea_field($_POST['bio']);

    if (empty($username) || empty($email) || empty($password)) {
        wp_send_json_error('Please fill in all required fields.');
    }

    if (username_exists($username) || email_exists($email)) {
        wp_send_json_error('Username or email already exists.');
    }

    $user_id = wp_create_user($username, $password, $email);
    if (is_wp_error($user_id)) {
        wp_send_json_error($user_id->get_error_message());
    }

    update_user_meta($user_id, 'mkwa_bio', $bio);

    // Initialize gamification features
    MKWAPointsSystem::add_points($user_id, 0, 'Welcome to MKWA Fitness');
    MKWABadgesSystem::assign_badge($user_id, 'Welcome Badge', 'Welcome to the MKWA community!');

    wp_send_json_success('Registration successful! You can now log in.');
}
add_action('wp_ajax_nopriv_mkwa_register_user', 'mkwa_register_user');
?>
