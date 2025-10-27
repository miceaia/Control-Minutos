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

        wp_enqueue_script( 'control-minutos-admin', plugins_url( 'assets/js/admin.js', CONTROL_MINUTOS_PLUGIN_FILE ), array( 'jquery', 'datatables', 'datatables-buttons', 'datatables-buttons-html5', 'datatables-buttons-print' ), CONTROL_MINUTOS_VERSION, true );

        wp_localize_script(
            'control-minutos-admin',
            'controlMinutosAdmin',
            array(
                'nonce'    => wp_create_nonce( 'wp_rest' ),
                'endpoint' => rest_url( Control_Minutos_REST::REST_NAMESPACE . '/progress' ),
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

        $users   = get_users( array( 'fields' => array( 'ID', 'display_name' ) ) );
        $courses = apply_filters( 'control_minutos_courses', array() );
        $lessons = apply_filters( 'control_minutos_lessons', array() );

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
}
