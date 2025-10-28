<?php
/**
 * Main plugin bootstrap.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-loader.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-i18n.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-integrations.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-rest.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-admin.php';
require_once CONTROL_MINUTOS_PLUGIN_DIR . 'includes/class-control-minutos-frontend.php';

/**
 * Coordinates plugin dependencies and hooks.
 */
class Control_Minutos {

    /**
     * Unique plugin identifier.
     *
     * @var string
     */
    protected $plugin_name = 'control-minutos';

    /**
     * Plugin version.
     *
     * @var string
     */
    protected $version;

    /**
     * Loader that orchestrates hooks.
     *
     * @var Control_Minutos_Loader
     */
    protected $loader;

    /**
     * Integrations helper.
     *
     * @var Control_Minutos_Integrations
     */
    protected $integrations;

    /**
     * REST controller.
     *
     * @var Control_Minutos_REST
     */
    protected $rest_controller;

    /**
     * Admin UI handler.
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
     * Plugin file path.
     *
     * @var string
     */
    protected $plugin_file;

    /**
     * Instantiate the plugin and register hooks.
     */
    public function __construct() {
        $this->version     = CONTROL_MINUTOS_VERSION;
        $this->plugin_file = CONTROL_MINUTOS_PLUGIN_FILE;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->define_rest_hooks();
    }

    /**
     * Load class dependencies.
     */
    protected function load_dependencies() {
        $this->loader        = new Control_Minutos_Loader();
        $this->integrations  = new Control_Minutos_Integrations();
        $this->rest_controller = new Control_Minutos_REST( $this->integrations );
        $this->admin         = new Control_Minutos_Admin( $this->plugin_name, $this->version, $this->integrations, $this->plugin_file );
        $this->frontend      = new Control_Minutos_Frontend( $this->plugin_name, $this->version, $this->rest_controller, $this->integrations, $this->plugin_file );
    }

    /**
     * Setup plugin localization.
     */
    protected function set_locale() {
        $plugin_i18n = new Control_Minutos_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register admin-specific hooks.
     */
    protected function define_admin_hooks() {
        $this->loader->add_action( 'admin_menu', $this->admin, 'register_menu' );
        $this->loader->add_action( 'admin_enqueue_scripts', $this->admin, 'enqueue_assets' );
    }

    /**
     * Register public hooks.
     */
    protected function define_public_hooks() {
        $this->loader->add_action( 'wp_enqueue_scripts', $this->frontend, 'enqueue_assets' );
    }

    /**
     * Register REST hooks.
     */
    protected function define_rest_hooks() {
        $this->loader->add_action( 'rest_api_init', $this->rest_controller, 'register_routes' );
    }

    /**
     * Run the plugin loader.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * Retrieve the loader instance.
     *
     * @return Control_Minutos_Loader
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the plugin name.
     *
     * @return string
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * Retrieve the plugin version.
     *
     * @return string
     */
    public function get_version() {
        return $this->version;
    }
}
