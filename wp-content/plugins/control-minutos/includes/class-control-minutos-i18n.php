<?php
/**
 * Handle internationalization concerns.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Loads the plugin text domain when WordPress initializes translations.
 */
class Control_Minutos_i18n {

    /**
     * Load plugin text domain.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'control-minutos',
            false,
            dirname( plugin_basename( CONTROL_MINUTOS_PLUGIN_FILE ) ) . '/languages/'
        );
    }
}
