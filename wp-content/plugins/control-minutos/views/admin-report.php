<?php
/** @var array $logs */
/** @var array $users */
/** @var array $courses */
/** @var array $lessons */

$totals = array(
    'consumed'  => 0,
    'remaining' => 0,
    'lessons'   => array(),
    'users'     => array(),
);

foreach ( $logs as $log ) {
    $consumed_seconds  = (int) $log['seconds_watched'];
    $total_seconds     = (int) $log['total_seconds'];
    $remaining_seconds = max( 0, $total_seconds - $consumed_seconds );

    $totals['consumed'] += $consumed_seconds;
    $totals['remaining'] += $remaining_seconds;
    if ( $log['lesson_id'] ) {
        $totals['lessons'][ $log['lesson_id'] ] = true;
    }
    if ( $log['user_id'] ) {
        $totals['users'][ $log['user_id'] ] = true;
    }
}

$total_consumed_minutes  = round( $totals['consumed'] / 60, 1 );
$total_remaining_minutes = round( $totals['remaining'] / 60, 1 );
$unique_lessons          = count( $totals['lessons'] );
$unique_users            = count( $totals['users'] );
?>
<div class="wrap control-minutos-wrap">
    <div class="control-minutos-header">
        <div>
            <h1><?php esc_html_e( 'Visualizaciones', 'control-minutos' ); ?></h1>
            <p class="subtitle"><?php esc_html_e( 'Monitorea el avance de los estudiantes y los minutos restantes por lección.', 'control-minutos' ); ?></p>
        </div>
    </div>
    <div class="control-minutos-summary">
        <div class="summary-card">
            <span class="label"><?php esc_html_e( 'Minutos consumidos', 'control-minutos' ); ?></span>
            <span class="value"><?php echo esc_html( $total_consumed_minutes ); ?></span>
        </div>
        <div class="summary-card">
            <span class="label"><?php esc_html_e( 'Minutos restantes', 'control-minutos' ); ?></span>
            <span class="value"><?php echo esc_html( $total_remaining_minutes ); ?></span>
        </div>
        <div class="summary-card">
            <span class="label"><?php esc_html_e( 'Lecciones con actividad', 'control-minutos' ); ?></span>
            <span class="value"><?php echo esc_html( $unique_lessons ); ?></span>
        </div>
        <div class="summary-card">
            <span class="label"><?php esc_html_e( 'Usuarios activos', 'control-minutos' ); ?></span>
            <span class="value"><?php echo esc_html( $unique_users ); ?></span>
        </div>
    </div>
    <div class="control-minutos-filters">
        <div class="filter-group">
            <label for="control-minutos-filter-curso"><?php esc_html_e( 'Curso', 'control-minutos' ); ?></label>
            <select id="control-minutos-filter-curso" class="control-select">
                <option value=""><?php esc_html_e( 'Todos', 'control-minutos' ); ?></option>
                <?php foreach ( $courses as $course_id => $course_name ) : ?>
                    <option value="<?php echo esc_attr( $course_name ); ?>"><?php echo esc_html( $course_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="control-minutos-filter-usuario"><?php esc_html_e( 'Usuario', 'control-minutos' ); ?></label>
            <select id="control-minutos-filter-usuario" class="control-select">
                <option value=""><?php esc_html_e( 'Todos', 'control-minutos' ); ?></option>
                <?php foreach ( $users as $user ) : ?>
                    <option value="<?php echo esc_attr( $user->display_name ); ?>"><?php echo esc_html( $user->display_name ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group search-group">
            <label for="control-minutos-search"><?php esc_html_e( 'Buscar', 'control-minutos' ); ?></label>
            <input type="search" id="control-minutos-search" placeholder="<?php echo esc_attr__( 'Usuario, curso o lección…', 'control-minutos' ); ?>" />
        </div>
    </div>
    <div class="control-minutos-actions">
        <span class="description"><?php esc_html_e( 'Exporta los datos o haz clic en “Detalles” para ver la ficha del video.', 'control-minutos' ); ?></span>
    </div>
    <div class="control-minutos-table-wrapper">
        <table id="control-minutos-table" class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e( 'Usuario', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Curso', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Lección', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Visualizado', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Restante', 'control-minutos' ); ?></th>
                    <th><?php esc_html_e( 'Acción', 'control-minutos' ); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ( $logs as $log ) :
                $course_name = $log['course_id'] && isset( $courses[ $log['course_id'] ] ) ? $courses[ $log['course_id'] ] : '';
                $lesson_name = $log['lesson_id'] && isset( $lessons[ $log['lesson_id'] ] ) ? $lessons[ $log['lesson_id'] ] : '';
                $consumed    = (int) $log['seconds_watched'];
                $total       = (int) $log['total_seconds'];
                $remaining   = max( 0, $total - $consumed );
                $progress    = $total > 0 ? min( 100, ( $consumed / $total ) * 100 ) : 0;
                ?>
                <tr data-user="<?php echo esc_attr( $log['user_id'] ); ?>" data-course="<?php echo esc_attr( $log['course_id'] ); ?>" data-lesson="<?php echo esc_attr( $log['lesson_id'] ); ?>">
                    <td><?php echo esc_html( $log['user_name'] ); ?></td>
                    <td><?php echo esc_html( $course_name ); ?></td>
                    <td><?php echo esc_html( $lesson_name ); ?></td>
                    <td data-order="<?php echo esc_attr( $progress ); ?>">
                        <div class="progress">
                            <span class="progress-value"><?php echo esc_html( round( $consumed / 60, 1 ) ); ?> / <?php echo esc_html( round( $total / 60, 1 ) ); ?> <?php esc_html_e( 'min', 'control-minutos' ); ?></span>
                            <div class="progress-bar">
                                <span class="progress-fill" style="width: <?php echo esc_attr( $progress ); ?>%"></span>
                            </div>
                        </div>
                    </td>
                    <td data-order="<?php echo esc_attr( $remaining ); ?>"><?php echo esc_html( round( $remaining / 60, 1 ) ); ?> <?php esc_html_e( 'min', 'control-minutos' ); ?></td>
                    <td><button class="button button-primary view-details" data-video="<?php echo esc_attr( $log['video_id'] ); ?>"><?php esc_html_e( 'Detalles', 'control-minutos' ); ?></button></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<script>
    window.controlMinutosLogs = <?php echo wp_json_encode( array_map(
        function ( $log ) use ( $courses, $lessons ) {
            $log['course_name']       = isset( $courses[ $log['course_id'] ] ) ? $courses[ $log['course_id'] ] : '';
            $log['lesson_name']       = isset( $lessons[ $log['lesson_id'] ] ) ? $lessons[ $log['lesson_id'] ] : '';
            $log['remaining_seconds'] = max( 0, (int) $log['total_seconds'] - (int) $log['seconds_watched'] );
            return $log;
        },
        $logs
    ) ); ?>;
</script>
