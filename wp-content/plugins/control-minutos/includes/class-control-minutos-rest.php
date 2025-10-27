<?php
/**
 * REST endpoints for logging watch time.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Control_Minutos_REST {
    const REST_NAMESPACE = 'control-minutos/v1';

    /**
     * Integrations helper.
     *
     * @var Control_Minutos_Integrations|null
     */
    protected $integrations;

    /**
     * Constructor.
     *
     * @param Control_Minutos_Integrations|null $integrations Integrations helper.
     */
    public function __construct( $integrations = null ) {
        $this->integrations = $integrations;
    }

    /**
     * Register hooks.
     */
    public function hooks() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST routes.
     */
    public function register_routes() {
        register_rest_route(
            self::REST_NAMESPACE,
            '/progress',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( $this, 'log_progress' ),
                'permission_callback' => array( $this, 'permission_callback' ),
            )
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/progress',
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_progress' ),
                'permission_callback' => array( $this, 'permission_callback' ),
                'args'                => array(
                    'video_id' => array(
                        'required' => true,
                        'type'     => 'string',
                    ),
                ),
            )
        );
    }

    /**
     * Determine if current user can update progress.
     *
     * @return bool
     */
    public function permission_callback() {
        return is_user_logged_in();
    }

    /**
     * Log playback progress.
     *
     * @param WP_REST_Request $request Request.
     *
     * @return WP_REST_Response
     */
    public function log_progress( WP_REST_Request $request ) {
        $user_id        = get_current_user_id();
        $video_id       = sanitize_text_field( $request->get_param( 'video_id' ) );
        $seconds_watched = absint( $request->get_param( 'seconds_watched' ) );
        $total_seconds   = absint( $request->get_param( 'total_seconds' ) );
        $course_id       = absint( $request->get_param( 'course_id' ) );
        $lesson_id       = absint( $request->get_param( 'lesson_id' ) );

        if ( $this->integrations && ( ! $course_id || ! $lesson_id ) ) {
            $resolved = $this->integrations->resolve_context_from_video( $video_id );

            if ( ! $course_id && ! empty( $resolved['course_id'] ) ) {
                $course_id = (int) $resolved['course_id'];
            }

            if ( ! $lesson_id && ! empty( $resolved['lesson_id'] ) ) {
                $lesson_id = (int) $resolved['lesson_id'];
            }
        }

        if ( empty( $video_id ) ) {
            return new WP_REST_Response( array( 'message' => __( 'Datos incompletos.', 'control-minutos' ) ), 400 );
        }

        if ( $this->integrations && empty( $total_seconds ) ) {
            $resolved_total = $this->integrations->get_avppro_duration( $video_id );

            if ( $resolved_total ) {
                $total_seconds = $resolved_total;
            }
        }

        if ( empty( $total_seconds ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'message' => __( 'Duración desconocida del video.', 'control-minutos' ),
                ),
                200
            );
        }

        global $wpdb;

        $table = $wpdb->prefix . 'control_minutos_logs';

        $existing_row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, seconds_watched FROM {$table} WHERE user_id = %d AND video_id = %s",
                $user_id,
                $video_id
            ),
            ARRAY_A
        );

        $seconds_to_store = min( $seconds_watched, $total_seconds );

        if ( $existing_row ) {
            $seconds_to_store = max( $seconds_to_store, (int) $existing_row['seconds_watched'] );
        }

        $data = array(
            'user_id'         => $user_id,
            'course_id'       => $course_id ?: null,
            'lesson_id'       => $lesson_id ?: null,
            'video_id'        => $video_id,
            'seconds_watched' => $seconds_to_store,
            'total_seconds'   => $total_seconds,
            'last_viewed'     => current_time( 'mysql', true ),
        );

        if ( $existing_row ) {
            $wpdb->update( $table, $data, array( 'id' => $existing_row['id'] ) );
        } else {
            $wpdb->insert( $table, $data );
        }

        return new WP_REST_Response( array( 'success' => true ), 200 );
    }

    /**
     * Get current user progress for a video.
     *
     * @param WP_REST_Request $request Request.
     *
     * @return WP_REST_Response
     */
    public function get_progress( WP_REST_Request $request ) {
        $user_id  = get_current_user_id();
        $video_id = sanitize_text_field( $request->get_param( 'video_id' ) );

        global $wpdb;

        $table = $wpdb->prefix . 'control_minutos_logs';

        $row = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT seconds_watched, total_seconds FROM {$table} WHERE user_id = %d AND video_id = %s",
                $user_id,
                $video_id
            ),
            ARRAY_A
        );

        if ( ! $row ) {
            return new WP_REST_Response(
                array(
                    'seconds_watched'   => 0,
                    'total_seconds'     => 0,
                    'remaining_seconds' => 0,
                ),
                200
            );
        }

        $row['remaining_seconds'] = max( 0, (int) $row['total_seconds'] - (int) $row['seconds_watched'] );

        return new WP_REST_Response( $row, 200 );
    }
}
