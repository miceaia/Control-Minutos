<?php
/**
 * Plugin activation helper.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Control_Minutos_Activator {
    /**
     * Run on activation.
     */
    public static function activate() {
        global $wpdb;

        $table_name      = $wpdb->prefix . 'control_minutos_logs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE {$table_name} (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            user_id bigint(20) unsigned NOT NULL,
            course_id bigint(20) unsigned DEFAULT NULL,
            lesson_id bigint(20) unsigned DEFAULT NULL,
            video_id varchar(190) NOT NULL,
            seconds_watched int(11) unsigned NOT NULL DEFAULT 0,
            total_seconds int(11) unsigned NOT NULL DEFAULT 0,
            last_viewed datetime NOT NULL,
            PRIMARY KEY  (id),
            KEY user_course (user_id, course_id),
            KEY lesson (lesson_id),
            KEY video (video_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Run on deactivation.
     */
    public static function deactivate() {
        // Intentionally left blank. Data is preserved on deactivation.
    }
}
