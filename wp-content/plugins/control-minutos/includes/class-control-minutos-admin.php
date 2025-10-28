<?php
/**
 * Admin UI for Control de Minutos.
 *
 * @package Control_Minutos
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Control_Minutos_Admin {
    /**
     * Integrations helper.
     *
     * @var Control_Minutos_Integrations
     */
    protected $integrations;

    /**
     * Constructor.
     *
     * @param Control_Minutos_Integrations $integrations Integrations helper.
     */
    public function __construct( Control_Minutos_Integrations $integrations ) {
        $this->integrations = $integrations;
    }

    /**
     * Register hooks.
     */
    public function hooks() {
        add_action( 'admin_menu', array( $this, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
    }

    /**
     * Register admin menu page.
     */
    public function register_menu() {
        add_menu_page(
            __( 'Control de Minutos', 'control-minutos' ),
            __( 'Visualizaciones', 'control-minutos' ),
            'manage_options',
            'control-minutos',
            array( $this, 'render_page' ),
            'dashicons-video-alt3'
        );
    }

    /**
     * Enqueue DataTables and custom styles.
     */
    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_control-minutos' !== $hook ) {
            return;
        }

        wp_enqueue_style( 'control-minutos-admin', plugins_url( 'assets/css/admin.css', CONTROL_MINUTOS_PLUGIN_FILE ), array(), CONTROL_MINUTOS_VERSION );
        wp_enqueue_style( 'datatables', 'https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css', array(), '1.13.6' );
        wp_enqueue_style( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css', array( 'datatables' ), '2.4.1' );
        wp_enqueue_script( 'datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array( 'jquery' ), '1.13.6', true );
        wp_enqueue_script( 'datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js', array( 'datatables' ), '2.4.1', true );
        wp_enqueue_script( 'datatables-buttons-html5', 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js', array( 'datatables-buttons' ), '2.4.1', true );
        wp_enqueue_script( 'datatables-buttons-print', 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js', array( 'datatables-buttons' ), '2.4.1', true );
        wp_enqueue_script( 'datatables-jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', array(), '3.10.1', true );
        wp_enqueue_script( 'datatables-pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js', array(), '0.2.7', true );
        wp_enqueue_script( 'datatables-vfs', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js', array( 'datatables-pdfmake' ), '0.2.7', true );

        wp_enqueue_script(
            'control-minutos-admin',
            plugins_url( 'assets/js/admin.js', CONTROL_MINUTOS_PLUGIN_FILE ),
            array( 'jquery', 'datatables', 'datatables-buttons', 'datatables-buttons-html5', 'datatables-buttons-print', 'wp-api-fetch' ),
            CONTROL_MINUTOS_VERSION,
            true
        );

        wp_localize_script(
            'control-minutos-admin',
            'controlMinutosAdmin',
            array(
                'nonce'        => wp_create_nonce( 'wp_rest' ),
                'endpoint'     => rest_url( Control_Minutos_REST::REST_NAMESPACE . '/progress' ),
                'restNamespace' => Control_Minutos_REST::REST_NAMESPACE,
                'restRoot'      => esc_url_raw( rest_url() ),
                'strings'      => array(
                    'detailsTitle' => __( 'Detalle de visualización', 'control-minutos' ),
                    'minutesShort' => __( 'min', 'control-minutos' ),
                    'remaining'    => __( 'Restan', 'control-minutos' ),
                    'consumed'     => __( 'Consumido', 'control-minutos' ),
                    'detailsButton' => __( 'Detalles', 'control-minutos' ),
                    'close'        => __( 'Cerrar', 'control-minutos' ),
                    'video'        => __( 'ID del video', 'control-minutos' ),
                ),
                'courseOptions' => $this->integrations->get_learndash_courses(),
                'lessonOptions' => $this->prepare_lessons_for_localize(),
            )
        );
    }

    /**
     * Render admin page.
     */
    public function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'No tienes permisos para acceder.', 'control-minutos' ) );
        }

        $users = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );

        $courses = $this->integrations->get_learndash_courses();
        $manual_courses = apply_filters( 'control_minutos_courses', array() );

        if ( is_array( $manual_courses ) && ! empty( $manual_courses ) ) {
            $courses = $manual_courses + $courses;
        }

        $lessons_map   = $this->integrations->get_learndash_lessons();
        $manual_lessons = apply_filters( 'control_minutos_lessons', array() );

        if ( is_array( $manual_lessons ) && ! empty( $manual_lessons ) ) {
            foreach ( $manual_lessons as $lesson_id => $lesson_name ) {
                $lessons_map[ $lesson_id ] = array(
                    'title'     => $lesson_name,
                    'course_id' => isset( $lessons_map[ $lesson_id ]['course_id'] ) ? $lessons_map[ $lesson_id ]['course_id'] : 0,
                );
            }
        }

        $lessons = array();
        foreach ( $lessons_map as $lesson_id => $data ) {
            $lessons[ $lesson_id ] = $data['title'];
        }

        global $wpdb;
        $table = $wpdb->prefix . 'control_minutos_logs';

        $logs = $wpdb->get_results(
            "SELECT l.*, u.display_name AS user_name
            FROM {$table} l
            LEFT JOIN {$wpdb->users} u ON u.ID = l.user_id
            ORDER BY l.last_viewed DESC",
            ARRAY_A
        );

        include CONTROL_MINUTOS_PLUGIN_DIR . 'views/admin-report.php';
    }

    /**
     * Prepare lesson data for localization.
     *
     * @return array<int,array{title:string,course_id:int}>
     */
    protected function prepare_lessons_for_localize() {
        $lessons = $this->integrations->get_learndash_lessons();
        $manual  = apply_filters( 'control_minutos_lessons', array() );

        if ( is_array( $manual ) && ! empty( $manual ) ) {
            foreach ( $manual as $lesson_id => $lesson_name ) {
                $lessons[ $lesson_id ] = array(
                    'title'     => $lesson_name,
                    'course_id' => isset( $lessons[ $lesson_id ]['course_id'] ) ? (int) $lessons[ $lesson_id ]['course_id'] : 0,
                );
            }
        }

        foreach ( $lessons as $lesson_id => $data ) {
            $lessons[ $lesson_id ] = array(
                'title'     => $data['title'],
                'course_id' => isset( $data['course_id'] ) ? (int) $data['course_id'] : 0,
            );
        }

        return $lessons;
    }
}
