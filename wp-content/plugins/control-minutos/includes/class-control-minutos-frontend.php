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
     * Constructor.
     *
     * @param Control_Minutos_REST        $rest_controller REST controller.
     * @param Control_Minutos_Integrations $integrations   Integrations helper.
     */
    public function __construct( Control_Minutos_REST $rest_controller, Control_Minutos_Integrations $integrations ) {
        $this->rest_controller = $rest_controller;
        $this->integrations    = $integrations;
        $default_selector      = '.avppro-player video, .miceaia-video-player video, [data-avppro-player] video';
        $this->selector        = apply_filters( 'control_minutos_video_selector', $default_selector );
    }

    /**
     * Register hooks.
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Enqueue scripts.
     */
    public function enqueue_assets() {
        wp_enqueue_style( 'control-minutos-frontend', plugins_url( 'assets/css/frontend.css', CONTROL_MINUTOS_PLUGIN_FILE ), array(), CONTROL_MINUTOS_VERSION );
        wp_enqueue_script( 'control-minutos-frontend', plugins_url( 'assets/js/frontend.js', CONTROL_MINUTOS_PLUGIN_FILE ), array( 'jquery', 'wp-api-fetch' ), CONTROL_MINUTOS_VERSION, true );

        $context = $this->integrations->get_current_learndash_context();

        wp_localize_script(
            'control-minutos-frontend',
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
