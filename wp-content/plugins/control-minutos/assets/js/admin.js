(function ($) {
    const settings = window.controlMinutosAdmin || {};
    const strings = settings.strings || {};
    const minutesLabel = strings.minutesShort || 'min';

    const formatMinutes = (seconds) => {
        const minutes = (Number(seconds) || 0) / 60;
        const rounded = Math.round(minutes * 10) / 10;
        return Number.isInteger(rounded) ? rounded.toString() : rounded.toFixed(1);
    };

    function buildTable(logs) {
        const tableBody = $('#control-minutos-table tbody');
        tableBody.empty();

        logs.forEach((log) => {
            const secondsWatched = Number(log.seconds_watched) || 0;
            const totalSeconds = Number(log.total_seconds) || 0;
            const remainingSeconds = Math.max(0, totalSeconds - secondsWatched);
            const progressPercent = totalSeconds > 0 ? Math.min(100, (secondsWatched / totalSeconds) * 100) : 0;
            const consumedMinutes = formatMinutes(secondsWatched);
            const totalMinutes = formatMinutes(totalSeconds);
            const remainingMinutes = formatMinutes(remainingSeconds);

            const row = `
                <tr data-user="${log.user_id}" data-course="${log.course_id || ''}" data-lesson="${log.lesson_id || ''}">
                    <td>${log.user_name || '-'}</td>
                    <td>${log.course_name || '-'}</td>
                    <td>${log.lesson_name || '-'}</td>
                    <td data-order="${progressPercent.toFixed(2)}">
                        <div class="progress">
                            <span class="progress-value">${consumedMinutes} / ${totalMinutes} ${minutesLabel}</span>
                            <div class="progress-bar">
                                <span class="progress-fill" style="width: ${progressPercent}%"></span>
                            </div>
                        </div>
                    </td>
                    <td data-order="${remainingSeconds}">${remainingMinutes} ${minutesLabel}</td>
                    <td>
                        <button
                            class="button button-primary view-details"
                            data-user="${log.user_name || ''}"
                            data-course="${log.course_name || ''}"
                            data-lesson="${log.lesson_name || ''}"
                            data-consumed="${consumedMinutes}"
                            data-total="${totalMinutes}"
                            data-remaining="${remainingMinutes}"
                            data-updated="${log.last_viewed || ''}"
                        >${strings.detailsButton || 'Detalles'}</button>
                    </td>
                </tr>
            `;

            tableBody.append(row);
        });
    }

    function initDataTable() {
        const table = $('#control-minutos-table');

        if (!table.length) {
            return;
        }

        const escapeRegex = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');

        $.fn.dataTable.ext.buttons.reset = {
            text: 'Reset',
            action: function (e, dt) {
                dt.search('').columns().search('').draw();
                $('#control-minutos-filter-usuario').val('');
                $('#control-minutos-filter-curso').val('');
                $('#control-minutos-search').val('');
            }
        };

        const datatable = table.DataTable({
            dom: 'Bfrtip',
            buttons: ['reset', 'copyHtml5', 'excelHtml5', 'print', 'pdfHtml5'],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },
            order: [[3, 'desc']],
        });

        $('#control-minutos-filter-usuario, #control-minutos-filter-curso').on('change', function () {
            const userFilter = $('#control-minutos-filter-usuario').val();
            const courseFilter = $('#control-minutos-filter-curso').val();

            datatable.column(0).search(userFilter ? `^${escapeRegex(userFilter)}$` : '', true, false);
            datatable.column(1).search(courseFilter ? `^${escapeRegex(courseFilter)}$` : '', true, false);
            datatable.draw();
        });

        $('#control-minutos-search').on('keyup change', function () {
            datatable.search($(this).val()).draw();
        });

        table.on('click', '.view-details', function () {
            const button = $(this);
            const detailLines = [
                button.data('user') && button.data('course') ? `${button.data('user')} · ${button.data('course')}` : button.data('user') || button.data('course'),
                button.data('lesson') ? `${button.data('lesson')}` : '',
                `${strings.consumed || 'Consumido'}: ${button.data('consumed')} ${minutesLabel}`,
                `${strings.remaining || 'Restan'}: ${button.data('remaining')} ${minutesLabel}`,
                button.data('updated') ? `Última visualización: ${button.data('updated')}` : ''
            ].filter(Boolean);

            window.alert(`${strings.detailsTitle || 'Detalle de visualización'}\n\n${detailLines.join('\n')}`);
        });
    }

    $(document).ready(function () {
        const logs = window.controlMinutosLogs || [];
        buildTable(logs);
        initDataTable();
    });
})(jQuery);
