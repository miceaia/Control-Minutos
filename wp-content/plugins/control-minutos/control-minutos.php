<?php
/**
 * Plugin Name: Control de Minutos
 * Description: Controla los minutos consumidos en Advanced Video Player Pro y muestra reportes por usuario y lección.
 * Version: 1.2.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 6.7.4
 * Author: Control-Minutos Team
 * Text Domain: control-minutos
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'CONTROL_MINUTOS_VERSION' ) ) {
    define( 'CONTROL_MINUTOS_VERSION', '1.2.0' );
}

if ( ! defined( 'CONTROL_MINUTOS_PLUGIN_FILE' ) ) {
    define( 'CONTROL_MINUTOS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'CONTROL_MINUTOS_PLUGIN_DIR' ) ) {
    define( 'CONTROL_MINUTOS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
}

require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos.php';

function control_minutos() {
    return Control_Minutos::instance();
}

control_minutos();
