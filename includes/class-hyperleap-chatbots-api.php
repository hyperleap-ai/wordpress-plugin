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

        $response = $this->make_api_request('/chatbot/validate', array(
            'chatbotId' => $chatbot_id,
            'privateKey' => $chatbot_seed
        ));

        if (is_wp_error($response)) {
            return array(
                'valid' => false,
                'message' => $response->get_error_message()
            );
        }

        $result = array(
            'valid' => $response['success'] ?? false,
            'message' => $response['message'] ?? __('Validation failed.', 'hyperleap-chatbots'),
            'data' => $response['data'] ?? array()
        );

        // Cache successful validations for 1 hour
        if ($result['valid']) {
            set_transient($cache_key, $result, HOUR_IN_SECONDS);
        }

        return $result;
    }

    public function get_chatbot_info($chatbot_id, $chatbot_seed) {
        $response = $this->make_api_request('/chatbot/info', array(
            'chatbotId' => $chatbot_id,
            'privateKey' => $chatbot_seed
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return $response['data'] ?? false;
    }

    public function get_chatbot_analytics($chatbot_id, $chatbot_seed, $date_range = '7d') {
        $response = $this->make_api_request('/chatbot/analytics', array(
            'chatbotId' => $chatbot_id,
            'privateKey' => $chatbot_seed,
            'range' => $date_range
        ));

        if (is_wp_error($response)) {
            return false;
        }

        return $response['data'] ?? false;
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
        $response = $this->make_api_request('/health', array(), 'GET');
        
        if (is_wp_error($response)) {
            return array(
                'success' => false,
                'message' => $response->get_error_message()
            );
        }

        return array(
            'success' => true,
            'message' => __('API connection successful', 'hyperleap-chatbots'),
            'data' => $response
        );
    }
}