<?php

namespace Blockbite\Blockbite\Controllers;

use WP_Error;
use WP_REST_Request;

class BlockHelperAI extends Controller {

    private static $encryption;

    public function __construct() {
        self::$encryption = new DataEncryption();
    }

    public function generate_styles(WP_REST_Request $request) {
        $image_url = $request['imageUrl'];

        if (empty($image_url)) {
            return new WP_Error('missing_image_url', 'Image URL is missing');
        }

        // Fetch the image and convert it to base64
        $base64_image = $this->fetch_image_as_base64($image_url);

        if (is_wp_error($base64_image)) {
            return $base64_image;  // Return error if image fetching fails
        }
        
        // Call the OpenAI API with the base64 image to generate Tailwind CSS classes
        $openai_response = $this->call_openai_api($base64_image);

        if (is_wp_error($openai_response)) {
            return $openai_response;  // Handle OpenAI errors
        }
        
        return rest_ensure_response(array('data' => $openai_response['data']));
    }

    private function fetch_image_as_base64($image_url) {
        // Get image data from the URL
        $response = wp_remote_get($image_url);

        if (is_wp_error($response)) {
            return new WP_Error('image_fetch_error', 'Error fetching the image from the URL');
        }

        $image_data = wp_remote_retrieve_body($response);

        if (empty($image_data)) {
            return new WP_Error('empty_image_data', 'Fetched image is empty');
        }

        // Get the image's MIME type
        $mime_type = wp_remote_retrieve_header($response, 'content-type');

        // Convert the image data to base64
        $base64_image = 'data:' . $mime_type . ';base64,' . base64_encode($image_data);

        return $base64_image;
    }

    private function call_openai_api($base64_image) {
        $encrypted_api_key = get_option('openai_api_key');  // Retrieve OpenAI API key
        $api_key = self::$encryption->decrypt($encrypted_api_key);  // Decrypt the API key
    
        if (empty($api_key)) {
            return new WP_Error('missing_api_key', 'OpenAI API key is missing. Please configure it in the Blockbite integrations settings.');
        }
    
        $prompt_text = 'Analyze the image and suggest Tailwind CSS classes based on its colors, theme, and aesthetics. Please ensure the classes you suggest cover aspects like background color, text color, padding, margin, flex/grid properties, and typography if applicable. The response should have classes for the parent as well as any children (wordpress blocks), in the form of an object. Keep the response limited to this object, without explanation. Only return the stringified JSON object without any additional text or formatting. Ensure stringified JSON is valid JSON; in particular the closing brackets should be }]]} instead of }}]}. Check response for syntax before returning the final JSON string. Example response with a two-column parent node with one child with red text and another with blue text: {"parentClass":"grid max-w-xl grid-cols-1 gap-x-8 gap-y-16 lg:max-w-none lg:grid-cols-3","template":[["core/paragraph",{"content":"Child 1 content","class":"text-red-500"}],["core/paragraph",{"content":"Child 2 content","class":"text-blue-500"}]]}';
    
        $url = 'https://api.openai.com/v1/chat/completions';  // OpenAI API endpoint
    
        $headers = array(
            'Authorization' => 'Bearer ' . $api_key,
            'Content-Type' => 'application/json',
        );
    
        $body = json_encode(array(
            'model' => 'gpt-4o-mini',
            'messages' => array(
                array('role' => 'user', 'content' => [
                    [
                        'type' => 'text',
                        'text' => $prompt_text,
                    ],
                    [
                        'type' => 'image_url',
                        'image_url' => [
                            'url' => $base64_image,
                        ],
                    ],
                ]),
            ),
            'temperature' => 0.7,
        ));
    
        // Perform the API request
        $response = wp_remote_post($url, array(
            'headers' => $headers,
            'body'    => $body,
            'timeout' => 30,
        ));
    
        if (is_wp_error($response)) {
            return new WP_Error('openai_error', $response->get_error_message());
        }
    
        $response_body = wp_remote_retrieve_body($response);
        $data = json_decode($response_body, true);
    
        if (isset($data['choices'][0]['message']['content'])) {
            $tailwind_classes = $data['choices'][0]['message']['content'];
            return array('data' => $tailwind_classes);
        } else {
            return new WP_Error('invalid_openai_response', $data['error']['message']);
        }
    }
}
