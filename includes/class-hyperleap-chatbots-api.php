<?php
/**
 * API handler for Hyperleap chatbot validation and integration
 */
class Hyperleap_Chatbots_API {

    private $api_url;
    private $timeout;

    public function __construct() {
        $this->api_url = HYPERLEAP_CHATBOTS_API_URL;
        $this->timeout = 10;
    }

    public function validate_chatbot($chatbot_id, $chatbot_seed) {
        if (empty($chatbot_id) || empty($chatbot_seed)) {
            return array(
                'valid' => false,
                'message' => __('Chatbot ID and seed are required.', 'hyperleap-chatbots')
            );
        }

        // Use transients for caching validation results
        $cache_key = 'hyperleap_validate_' . md5($chatbot_id . $chatbot_seed);
        $cached_result = get_transient($cache_key);
        
        if ($cached_result !== false) {
            return $cached_result;
        }

        // Use the correct endpoint pattern from your React app
        $endpoint = '/api/chatbots/public/' . urlencode($chatbot_id) . '?key=' . urlencode($chatbot_seed);
        $response = $this->make_api_request($endpoint, array(), 'GET');

        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'message' => $response->get_error_message()
            );
        }

        // If we get a 200 response with chatbot data, it's valid
        $result = array(
            'valid' => true,
            'message' => __('Chatbot credentials are valid!', 'hyperleap-chatbots'),
            'data' => array(
                'name' => $response['chatbotName'] ?? $response['name'] ?? 'Chatbot',
                'description' => $response['description'] ?? '',
                'chatbot_info' => $response
            )
        );

        // Cache successful validations for 1 hour
        set_transient($cache_key, $result, HOUR_IN_SECONDS);

        return $result;
    }

    public function get_chatbot_info($chatbot_id, $chatbot_seed) {
        // Reuse the validation endpoint since it returns chatbot info
        $validation = $this->validate_chatbot($chatbot_id, $chatbot_seed);
        
        if ($validation['valid']) {
            return $validation['data']['chatbot_info'] ?? false;
        }

        return false;
    }

    public function get_chatbot_analytics($chatbot_id, $chatbot_seed, $date_range = '7d') {
        // Analytics endpoint might not be available for public API
        // This would require authentication and proper API endpoints
        // For now, return false to indicate feature not available
        return false;
    }

    private function make_api_request($endpoint, $data = array(), $method = 'POST') {
        $url = rtrim($this->api_url, '/') . $endpoint;
        
        $args = array(
            'timeout' => $this->timeout,
            'headers' => array(
                'Content-Type' => 'application/json',
                'User-Agent' => 'HyperleapChatbotsWP/' . HYPERLEAP_CHATBOTS_VERSION
            )
        );

        if ($method === 'POST') {
            $args['method'] = 'POST';
            $args['body'] = wp_json_encode($data);
        } else {
            $args['method'] = 'GET';
            if (!empty($data)) {
                $url = add_query_arg($data, $url);
            }
        }

        $response = wp_remote_request($url, $args);

        if (is_wp_error($response)) {
            error_log('Hyperleap API Error: ' . $response->get_error_message());
            return $response;
        }

        $code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        if ($code !== 200) {
            $error_message = sprintf(__('API request failed with status %d', 'hyperleap-chatbots'), $code);
            error_log('Hyperleap API Error: ' . $error_message . ' - ' . $body);
            return new WP_Error('api_error', $error_message);
        }

        $decoded = json_decode($body, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            $error_message = __('Invalid JSON response from API', 'hyperleap-chatbots');
            error_log('Hyperleap API Error: ' . $error_message . ' - ' . $body);
            return new WP_Error('json_error', $error_message);
        }

        return $decoded;
    }

    public function test_connection() {
        // Test connection by making a simple request to a known endpoint
        // We'll use a dummy chatbot validation to test if the API is reachable
        $url = rtrim($this->api_url, '/') . '/api/chatbots/public/test';
        
        $args = array(
            'timeout' => $this->timeout,
            'headers' => array(
                'User-Agent' => 'HyperleapChatbotsWP/' . HYPERLEAP_CHATBOTS_VERSION
            ),
            'method' => 'GET'
        );

        $response = wp_remote_request($url, $args);
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        $code = wp_remote_retrieve_response_code($response);
        
        // Even if we get 404 for the test endpoint, it means the API is reachable
        if ($code >= 200 && $code < 500) {
            return array(
                'success' => true,
                'message' => __('API connection successful', 'hyperleap-chatbots'),
                'data' => array('status_code' => $code)
            );
        }

        return array(
            'success' => false,
            'message' => sprintf(__('API connection failed with status %d', 'hyperleap-chatbots'), $code)
        );
    }
}