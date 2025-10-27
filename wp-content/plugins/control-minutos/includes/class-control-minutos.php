<?php
/**
 * Main plugin loader.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-activator.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-integrations.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-rest.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-admin.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-frontend.php';

class Control_Minutos {
    /**
     * Singleton instance.
     *
     * @var Control_Minutos
     */
    protected static $instance = null;

    /**
     * REST controller instance.
     *
     * @var Control_Minutos_REST
     */
    protected $rest_controller;

    /**
     * Integrations helper instance.
     *
     * @var Control_Minutos_Integrations
     */
    protected $integrations;

    /**
     * Admin UI instance.
     *
     * @var Control_Minutos_Admin
     */
    protected $admin;

    /**
     * Frontend handler.
     *
     * @var Control_Minutos_Frontend
     */
    protected $frontend;

    /**
     * Get singleton instance.
     *
     * @return Control_Minutos
     */
    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Control_Minutos constructor.
     */
    private function __construct() {
        register_activation_hook( CONTROL_MINUTOS_PLUGIN_FILE, array( 'Control_Minutos_Activator', 'activate' ) );
        register_deactivation_hook( CONTROL_MINUTOS_PLUGIN_FILE, array( 'Control_Minutos_Activator', 'deactivate' ) );

        add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
        add_action( 'init', array( $this, 'init' ) );
    }

    /**
     * Load plugin textdomain.
     */
    public function load_textdomain() {
        load_plugin_textdomain( 'control-minutos', false, dirname( plugin_basename( CONTROL_MINUTOS_PLUGIN_FILE ) ) . '/languages' );
    }

    /**
     * Init plugin components.
     */
    public function init() {
        $this->integrations    = new Control_Minutos_Integrations();
        $this->rest_controller = new Control_Minutos_REST( $this->integrations );
        $this->admin           = new Control_Minutos_Admin( $this->integrations );
        $this->frontend        = new Control_Minutos_Frontend( $this->rest_controller, $this->integrations );

        $this->rest_controller->hooks();
        $this->admin->hooks();
        $this->frontend->hooks();
    }
}
