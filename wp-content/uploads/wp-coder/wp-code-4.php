<?php

  defined( 'ABSPATH' ) || exit;
add_action('rest_api_init', function () {
    // Modify the users endpoint to allow searching by email
    register_rest_route('wp/v2', '/users/email', [
        'methods' => 'GET',
        'callback' => 'get_user_by_email_api',
        'permission_callback' => "__return_true"
        // function () {
        //     return current_user_can('list_users'); // Restrict access to admins or authorized users
        // },
    ]);
});

function get_user_by_email_api(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email')); // Get the email from the request

    // Validate the email
    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Invalid email address.', ['status' => 400]);
    }

    // Get user by email
    $user = get_user_by('email', $email);

    if (!$user) {
        // Return an empty response if no user is found
        return new WP_Error('not_found', 'User not found.', ['status' => 404]);
    }

    // Prepare and return the user data
    return [
        'id' => $user->ID,
        'email' => $user->user_email,
        'name' => $user->display_name,
    ];
}
