<?php
/**
 * Fired during plugin activation
 */
class Website_Chatbots_Activator {

    /**
     * Initialize plugin settings and create necessary database tables
     */
    public static function activate() {
        // Add default options if they don't exist
        if (!get_option('website_chatbots_data')) {
            add_option('website_chatbots_data', array());
        }

        // Set plugin version
        if (!get_option('website_chatbots_version')) {
            add_option('website_chatbots_version', WEBSITE_CHATBOTS_VERSION);
        }

        // Clear any cached data
        wp_cache_flush();

        // Ensure proper permalinks
        flush_rewrite_rules();
    }
}