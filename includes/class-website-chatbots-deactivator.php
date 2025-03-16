<?php
/**
 * Fired during plugin deactivation
 */
class Website_Chatbots_Deactivator {

    /**
     * Clean up plugin data and settings if necessary
     */
    public static function deactivate() {
        // Note: We don't delete the chatbot settings on deactivation
        // This ensures that user configurations are preserved if they reactivate the plugin
        // Settings can be removed manually using the WordPress cleanup routines if needed

        // Clear any cached data
        wp_cache_flush();

        // Reset permalinks
        flush_rewrite_rules();
    }
}