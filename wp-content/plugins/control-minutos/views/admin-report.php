<?php
/** @var array $logs */
/** @var array $users */
/** @var array $courses */
/** @var array $lessons */
?>
<div class="wrap control-minutos-wrap">
    <h1><?php esc_html_e( 'Visualizaciones', 'control-minutos' ); ?></h1>
    <div class="control-minutos-filters">
        <div>
            <label for="control-minutos-filter-curso"><?php esc_html_e( 'Curso', 'control-minutos' ); ?></label><br />
            <select id="control-minutos-filter-curso">
                <option value=""><?php esc_html_e( 'Todos', 'control-minutos' ); ?></option>
                <?php foreach ( $courses as $course_id => $course_name ) : ?>
                    <option value="<?php echo esc_attr( $course_name ); ?>"><?php echo esc_html( $course_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="control-minutos-filter-usuario"><?php esc_html_e( 'Usuario', 'control-minutos' ); ?></label><br />
            <select id="control-minutos-filter-usuario">
                <option value=""><?php esc_html_e( 'Todos', 'control-minutos' ); ?></option>
                <?php foreach ( $users as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->display_name ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="control-minutos-actions">
        <span class="description"><?php esc_html_e( 'Utiliza los botones para exportar la tabla.', 'control-minutos' ); ?></span>
    </div>
    <div class="control-minutos-table-wrapper">
        <table id="control-minutos-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Usuario', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Curso', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Lección', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Visualizado', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Acción', 'control-minutos' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $logs as $log ) :
                $course_name = $log['course_id'] && isset( $courses[ $log['course_id'] ] ) ? $courses[ $log['course_id'] ] : '';
                $lesson_name = $log['lesson_id'] && isset( $lessons[ $log['lesson_id'] ] ) ? $lessons[ $log['lesson_id'] ] : '';
                ?>
                <tr data-user="<?php echo esc_attr( $log['user_id'] ); ?>" data-course="<?php echo esc_attr( $log['course_id'] ); ?>" data-lesson="<?php echo esc_attr( $log['lesson_id'] ); ?>">
                    <td><?php echo esc_html( $log['user_name'] ); ?></td>
                    <td><?php echo esc_html( $course_name ); ?></td>
                    <td><?php echo esc_html( $lesson_name ); ?></td>
                    <td><?php echo esc_html( round( $log['seconds_watched'] / 60, 1 ) ); ?> / <?php echo esc_html( round( $log['total_seconds'] / 60, 1 ) ); ?></td>
                    <td><button class="button view-details" data-video="<?php echo esc_attr( $log['video_id'] ); ?>"><?php esc_html_e( 'Detalles', 'control-minutos' ); ?></button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.controlMinutosLogs = <?php echo wp_json_encode( array_map(
        function ( $log ) use ( $courses, $lessons ) {
            $log['course_name'] = isset( $courses[ $log['course_id'] ] ) ? $courses[ $log['course_id'] ] : '';
            $log['lesson_name'] = isset( $lessons[ $log['lesson_id'] ] ) ? $lessons[ $log['lesson_id'] ] : '';
            return $log;
        },
        $logs
    ) ); ?>;
</script>
