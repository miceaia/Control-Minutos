<?php
/**
 * Frontend integration with Advanced Video Player Pro.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Control_Minutos_Frontend {

    /**
     * Plugin slug for asset handles.
     *
     * @var string
     */
    protected $plugin_name;

    /**
     * Plugin version.
     *
     * @var string
     */
    protected $version;

    /**
     * REST controller instance.
     *
     * @var Control_Minutos_REST
     */
    protected $rest_controller;

    /**
     * Integrations helper.
     *
     * @var Control_Minutos_Integrations
     */
    protected $integrations;

    /**
     * CSS selector filter for Advanced Video Player Pro videos.
     *
     * @var string
     */
    protected $selector;

    /**
     * Absolute plugin file path for resolving URLs.
     *
     * @var string
     */
    protected $plugin_file;

    /**
     * Constructor.
     *
     * @param string                         $plugin_name     Plugin slug.
     * @param string                         $version         Plugin version.
     * @param Control_Minutos_REST           $rest_controller REST controller.
     * @param Control_Minutos_Integrations   $integrations    Integrations helper.
     * @param string                         $plugin_file     Plugin file path.
     */
    public function __construct( $plugin_name, $version, Control_Minutos_REST $rest_controller, Control_Minutos_Integrations $integrations, $plugin_file ) {
        $this->plugin_name    = $plugin_name;
        $this->version        = $version;
        $this->rest_controller = $rest_controller;
        $this->integrations   = $integrations;
        $this->plugin_file    = $plugin_file;

        $default_selector = '.avppro-player video, .miceaia-video-player video, [data-avppro-player] video';
        $this->selector   = apply_filters( 'control_minutos_video_selector', $default_selector );
    }

    /**
     * Enqueue frontend assets.
     */
    public function enqueue_assets() {
        $plugin_url   = plugin_dir_url( $this->plugin_file );
        $style_handle = $this->plugin_name . '-frontend';
        $script_handle = $this->plugin_name . '-frontend';

        wp_enqueue_style( $style_handle, $plugin_url . 'assets/css/frontend.css', array(), $this->version );
        wp_enqueue_script( $script_handle, $plugin_url . 'assets/js/frontend.js', array( 'jquery', 'wp-api-fetch' ), $this->version, true );

        $context = $this->integrations->get_current_learndash_context();

        wp_localize_script(
            $script_handle,
            'controlMinutosFrontend',
            array(
                'selector'      => $this->selector,
                'nonce'         => wp_create_nonce( 'wp_rest' ),
                'endpoint'      => rest_url( Control_Minutos_REST::REST_NAMESPACE . '/progress' ),
                'restNamespace' => Control_Minutos_REST::REST_NAMESPACE,
                'restRoot'      => esc_url_raw( rest_url() ),
                'context'       => array(
                    'courseId' => $context['course_id'],
                    'lessonId' => $context['lesson_id'],
                ),
                'integrations'  => array(
                    'learndashActive' => $this->integrations->is_learndash_active(),
                ),
                'strings'       => array(
                    'consumed'  => __( 'Consumido', 'control-minutos' ),
                    'unit'      => __( 'min', 'control-minutos' ),
                    'remaining' => __( 'Restan', 'control-minutos' ),
                ),
            )
        );
    }
}
