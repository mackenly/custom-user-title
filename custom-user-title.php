<?php
/*
Plugin Name: Custom User Title
Description: Adds a title field to user profiles accessible via REST API and GraphQL.
Version: 1.1.0
Author: Mackenly Jones
*/

// Add the title field to the user profile form
add_action('show_user_profile', 'add_custom_user_title_field');
add_action('edit_user_profile', 'add_custom_user_title_field');

function add_custom_user_title_field($user)
{
?>
    <h3>Extra profile information</h3>
    <table class="form-table">
        <tr>
            <th><label for="title">Title</label></th>
            <td>
                <input type="text" name="title" id="title" value="<?php echo esc_attr(get_user_meta($user->ID, 'title', true)); ?>" class="regular-text" /><br />
                <span class="description">Please enter your title.</span>
            </td>
        </tr>
    </table>
<?php
}

// Save the title field value
add_action('personal_options_update', 'save_custom_user_title_field');
add_action('edit_user_profile_update', 'save_custom_user_title_field');

function save_custom_user_title_field($user_id)
{
    if (!current_user_can('edit_user', $user_id))
        return false;

    update_user_meta($user_id, 'title', $_POST['title']);
}

// Add the title field to REST API responses
add_action('rest_api_init', function () {
    register_rest_field('user', 'title', [
        'get_callback' => function ($user, $field_name, $request) {
            return get_user_meta($user['id'], $field_name, true);
        },
        'update_callback' => function ($value, $user, $field_name) {
            update_user_meta($user->ID, $field_name, sanitize_text_field($value));
        },
        'schema' => [
            'type' => 'string',
            'description' => 'Title of the user',
            'single' => true,
            'show_in_rest' => true
        ],
    ]);
});

// Ensure WPGraphQL is active
add_action('graphql_register_types', function () {
    register_graphql_field('User', 'title', [
        'type' => 'String',
        'description' => 'The user\'s title',
        'resolve' => function ($user) {
            return get_user_meta($user->userId, 'title', true);
        }
    ]);
});