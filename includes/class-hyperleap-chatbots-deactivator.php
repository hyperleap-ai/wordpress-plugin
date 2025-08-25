<?php
/**
 * Fired during plugin deactivation
 */
class Hyperleap_Chatbots_Deactivator {

    public static function deactivate() {
        self::unschedule_cron_jobs();
        self::clear_caches();
        self::cleanup_temp_data();
        
        // Flush rewrite rules
        flush_rewrite_rules();
    }

    private static function unschedule_cron_jobs() {
        wp_clear_scheduled_hook('hyperleap_chatbots_cleanup');
        wp_clear_scheduled_hook('hyperleap_chatbots_sync');
    }

    private static function clear_caches() {
        // Clear validation cache
        global $wpdb;
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_hyperleap_validate_%'
            )
        );
        
        // Clear timeout transients as well
        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
                '_transient_timeout_hyperleap_validate_%'
            )
        );
    }

    private static function cleanup_temp_data() {
        delete_transient('hyperleap_chatbots_activation_redirect');
        delete_transient('hyperleap_chatbots_migration_notice');
    }
}