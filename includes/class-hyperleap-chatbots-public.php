<?php
/**
 * Public-facing functionality with performance optimizations
 */
class Hyperleap_Chatbots_Public {

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_public_assets() {
        // Only load if there are active chatbots
        if (!$this->has_active_chatbots()) {
            return;
        }

        wp_enqueue_style(
            $this->plugin_name . '-public',
            HYPERLEAP_CHATBOTS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            $this->version,
            'all'
        );
    }

    public function inject_chatbot_script() {
        $chatbot = $this->get_active_chatbot_for_current_page();
        
        if (!$chatbot) {
            return;
        }

        $this->output_chatbot_script($chatbot);
    }

    public function inject_chatbot_fallback() {
        // Fallback injection for themes that don't properly support wp_head
        if (!did_action('wp_head')) {
            $this->inject_chatbot_script();
        }
    }

    public function register_rest_routes() {
        register_rest_route('hyperleap-chatbots/v1', '/status', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_chatbot_status'),
            'permission_callback' => '__return_true'
        ));

        register_rest_route('hyperleap-chatbots/v1', '/config/(?P<id>[a-zA-Z0-9_]+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_chatbot_config'),
            'permission_callback' => '__return_true'
        ));
    }

    public function get_chatbot_status() {
        $chatbots = get_option('hyperleap_chatbots_data', array());
        $active_count = count(array_filter($chatbots, function($bot) { return $bot['enabled']; }));
        
        return new WP_REST_Response(array(
            'status' => 'ok',
            'total_chatbots' => count($chatbots),
            'active_chatbots' => $active_count,
            'plugin_version' => $this->version
        ));
    }

    public function get_chatbot_config($request) {
        $id = $request['id'];
        $chatbots = get_option('hyperleap_chatbots_data', array());
        
        if (!isset($chatbots[$id]) || !$chatbots[$id]['enabled']) {
            return new WP_Error('not_found', 'Chatbot not found or disabled', array('status' => 404));
        }
        
        $chatbot = $chatbots[$id];
        
        return new WP_REST_Response(array(
            'chatbotId' => $chatbot['chatbot_id'],
            'settings' => $chatbot['settings'],
            'enabled' => $chatbot['enabled']
        ));
    }

    private function has_active_chatbots() {
        $chatbots = get_option('hyperleap_chatbots_data', array());
        return !empty(array_filter($chatbots, function($bot) { return $bot['enabled']; }));
    }

    private function get_active_chatbot_for_current_page() {
        global $post;
        
        $chatbots = get_option('hyperleap_chatbots_data', array());
        
        foreach ($chatbots as $chatbot) {
            if (!$chatbot['enabled']) {
                continue;
            }

            if ($this->should_display_chatbot($chatbot, $post)) {
                return $chatbot;
            }
        }
        
        return null;
    }

    private function should_display_chatbot($chatbot, $post) {
        $placement = $chatbot['placement'] ?? 'sitewide';
        
        switch ($placement) {
            case 'sitewide':
                return $this->check_exclusions($chatbot, $post);
                
            case 'specific':
                return $this->check_specific_pages($chatbot, $post) && $this->check_exclusions($chatbot, $post);
                
            case 'homepage':
                return is_front_page() && $this->check_exclusions($chatbot, $post);
                
            case 'posts':
                return is_single() && $this->check_exclusions($chatbot, $post);
                
            case 'pages':
                return is_page() && $this->check_exclusions($chatbot, $post);
                
            default:
                return false;
        }
    }

    private function check_specific_pages($chatbot, $post) {
        if (!$post || empty($chatbot['pages'])) {
            return false;
        }
        
        return in_array($post->ID, $chatbot['pages']);
    }

    private function check_exclusions($chatbot, $post) {
        if (!$post || empty($chatbot['exclude_pages'])) {
            return true;
        }
        
        return !in_array($post->ID, $chatbot['exclude_pages']);
    }

    private function output_chatbot_script($chatbot) {
        if (empty($chatbot['chatbot_id']) || empty($chatbot['chatbot_seed'])) {
            return;
        }
        
        // Generate unique config variable name
        $config_var = 'hyperleapChatbotConfig_' . substr(md5($chatbot['id']), 0, 8);
        
        ?>
        <script type="text/javascript">
        (function() {
            'use strict';
            
            // Prevent multiple instances
            if (window.hyperleapChatbotLoaded) {
                return;
            }
            
            // Define chatbot configuration
            window.<?php echo esc_js($config_var); ?> = {
                chatbotId: <?php echo wp_json_encode($chatbot['chatbot_id']); ?>,
                privateKey: <?php echo wp_json_encode($chatbot['chatbot_seed']); ?>,
                settings: <?php echo wp_json_encode($chatbot['settings'] ?? array()); ?>,
                version: <?php echo wp_json_encode($this->version); ?>
            };
            
            // Use the global config for backward compatibility
            window.userChatbotConfig = window.<?php echo esc_js($config_var); ?>;
            
            // Load chatbot script with error handling and performance optimization
            function loadHyperleapChatbot() {
                const script = document.createElement('script');
                script.src = <?php echo wp_json_encode(HYPERLEAP_CHATBOTS_JS_URL); ?>;
                script.async = true;
                script.defer = true;
                
                script.onload = function() {
                    window.hyperleapChatbotLoaded = true;
                    console.log('Hyperleap Chatbot loaded successfully');
                };
                
                script.onerror = function() {
                    console.warn('Failed to load Hyperleap Chatbot');
                };
                
                // Append to head for better performance
                (document.head || document.documentElement).appendChild(script);
            }
            
            // Load immediately if DOM is ready, otherwise wait
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', loadHyperleapChatbot);
            } else {
                loadHyperleapChatbot();
            }
            
        })();
        </script>
        <?php
        
        // Add performance and analytics metadata
        echo "\n<!-- Hyperleap AI Chatbot v{$this->version} -->\n";
    }
}