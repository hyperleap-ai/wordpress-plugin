<?php
/**
 * Fired during plugin activation
 */
class Hyperleap_Chatbots_Activator {

    public static function activate() {
        self::create_default_options();
        self::schedule_cron_jobs();
        self::set_capabilities();
        self::maybe_migrate_old_data();
        
        // Flush rewrite rules for REST API endpoints
        flush_rewrite_rules();
        
        // Set activation flag for welcome notice
        set_transient('hyperleap_chatbots_activation_redirect', true, 30);
    }

    private static function create_default_options() {
        $default_settings = array(
            'js_url' => HYPERLEAP_CHATBOTS_JS_URL,
            'api_url' => HYPERLEAP_CHATBOTS_API_URL,
            'performance_mode' => true,
            'cache_validation' => true,
            'error_logging' => true,
            'analytics_enabled' => true,
            'auto_updates' => true
        );

        add_option('hyperleap_chatbots_settings', $default_settings);
        add_option('hyperleap_chatbots_data', array());
        add_option('hyperleap_chatbots_version', HYPERLEAP_CHATBOTS_VERSION);
    }

    private static function schedule_cron_jobs() {
        if (!wp_next_scheduled('hyperleap_chatbots_cleanup')) {
            wp_schedule_event(time(), 'daily', 'hyperleap_chatbots_cleanup');
        }
        
        if (!wp_next_scheduled('hyperleap_chatbots_sync')) {
            wp_schedule_event(time(), 'hourly', 'hyperleap_chatbots_sync');
        }
    }

    private static function set_capabilities() {
        $role = get_role('administrator');
        if ($role) {
            $role->add_cap('manage_hyperleap_chatbots');
        }
    }

    private static function maybe_migrate_old_data() {
        $old_data = get_option('website_chatbots_data');
        
        if (!empty($old_data) && is_array($old_data)) {
            $new_data = array();
            
            foreach ($old_data as $old_id => $old_chatbot) {
                $new_id = 'migrated_' . $old_id;
                
                $new_data[$new_id] = array(
                    'id' => $new_id,
                    'chatbot_id' => $old_chatbot['chatbot_id'] ?? '',
                    'chatbot_seed' => $old_chatbot['private_key'] ?? '',
                    'name' => $old_chatbot['chatbot_name'] ?? 'Migrated Chatbot',
                    'enabled' => ($old_chatbot['status'] ?? 'inactive') === 'active',
                    'placement' => self::convert_old_location($old_chatbot['location'] ?? 'sitewide'),
                    'pages' => $old_chatbot['pages'] ?? array(),
                    'exclude_pages' => array(),
                    'settings' => array(
                        'position' => 'bottom-right',
                        'theme' => 'auto'
                    ),
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );
            }
            
            update_option('hyperleap_chatbots_data', $new_data);
            
            // Keep old data as backup
            update_option('website_chatbots_data_backup', $old_data);
            delete_option('website_chatbots_data');
            
            set_transient('hyperleap_chatbots_migration_notice', count($new_data), 300);
        }
    }

    private static function convert_old_location($old_location) {
        switch ($old_location) {
            case 'sitewide':
                return 'sitewide';
            case 'specific':
                return 'specific';
            default:
                return 'sitewide';
        }
    }
}