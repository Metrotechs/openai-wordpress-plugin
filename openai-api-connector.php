<?php
/*
Plugin Name: Metrotechs OpenAI API Connector
Plugin URI: https://www.metrotechs.net
Description: A WordPress plugin that connects to the OpenAI API and allows users to interact with the API on the frontend.
Version: 1.0
Author: Metrotechs
Author URI: https://www.metrotechs.net
License: GPLv2 or later
Text Domain: metrotechs-openai-api-connector
*/

/*Register the plugin's JavaScript and CSS files for the frontend*/

function openai_api_connector_enqueue_scripts() {
    wp_enqueue_style('openai-api-connector', plugins_url('css/style.css', __FILE__));
    wp_enqueue_script('openai-api-connector', plugins_url('js/script.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'openai_api_connector_enqueue_scripts');

/*Add a shortcode to display the OpenAI API interaction form on the frontend*/

function openai_api_connector_shortcode($atts) {
    ob_start();
    include(plugin_dir_path(__FILE__) . 'templates/form.php');
    return ob_get_clean();
}
add_shortcode('openai-api-connector', 'openai_api_connector_shortcode');

// Define the AJAX URL and security nonce
function openai_api_connector_localize_scripts() {
    wp_localize_script('openai-api-connector', 'openaiApiConnector', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'security' => wp_create_nonce('openai_api_connector_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'openai_api_connector_localize_scripts');

// Add the AJAX handler function for processing requests
function openai_api_connector_process_request() {
    // Check the security nonce
    check_ajax_referer('openai_api_connector_nonce', 'security');

    $user_input = sanitize_textarea_field($_POST['input']);

    // Send the request to the OpenAI API
    $response = openai_api_connector_send_request($user_input);

    // Return the response
    echo $response;
    wp_die();
}
add_action('wp_ajax_openai_api_connector_process_request', 'openai_api_connector_process_request');
add_action('wp_ajax_nopriv_openai_api_connector_process_request', 'openai_api_connector_process_request');

// Function to send the request to the OpenAI API
function openai_api_connector_send_request($input) {
    // Replace with your OpenAI API key
    $api_key = '********************************';

    // Set up the request headers
    $headers = array(
        'Authorization' => 'Bearer ' . $api_key,
        'Content-Type' => 'application/json'
    );

    // Set up the request data
$data = array(
    'prompt' => 'You are an AI assistant engaged in a conversation with a user. You job is to respond to questions. Do not deviate in your response. The user said: "' . $input . '". Respond appropriately in a conversational manner. Do not display the prompt in your response.',
    'max_tokens' => 100,
    'temperature' => 0.7, // Adjust the temperature for a balance between focus and creativity
    'top_p' => 0.9 // Control the diversity of the generated text
);

    // Send the request to the OpenAI API
$response = wp_remote_post('https://api.openai.com/v1/engines/davinci/completions', array(
    'headers' => $headers,
    'body' => json_encode($data),
    'timeout' => 60
));
    // Check for errors and return the API response
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200) {
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        $output_text = $response_body['choices'][0]['text'];

        // Remove any unwanted characters or code snippets
        $clean_output = strip_tags($output_text);

        return $clean_output;
    } else {
        return 'An error occurred while processing your request.';
    }
}