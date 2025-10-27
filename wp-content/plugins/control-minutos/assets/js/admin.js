(function ($) {
    function buildTable(logs) {
        const tableBody = $('#control-minutos-table tbody');
        tableBody.empty();

        logs.forEach((log) => {
            const consumedMinutes = Math.round((log.seconds_watched / 60) * 10) / 10;
            const totalMinutes = Math.round((log.total_seconds / 60) * 10) / 10;

            const row = `
                <tr data-user="${log.user_id}" data-course="${log.course_id || ''}" data-lesson="${log.lesson_id || ''}">
                    <td>${log.user_name || '-'}</td>
                    <td>${log.course_name || '-'}</td>
                    <td>${log.lesson_name || '-'}</td>
                    <td>${consumedMinutes} / ${totalMinutes}</td>
                    <td><button class="button view-details" data-video="${log.video_id}">Detalles</button></td>
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
            }
        };

        const datatable = table.DataTable({
            dom: 'Bfrtip',
            buttons: ['reset', 'copyHtml5', 'excelHtml5', 'print', 'pdfHtml5'],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

        $('#control-minutos-filter-usuario, #control-minutos-filter-curso').on('change', function () {
            const userFilter = $('#control-minutos-filter-usuario').val();
            const courseFilter = $('#control-minutos-filter-curso').val();

            datatable.column(0).search(userFilter ? `^${escapeRegex(userFilter)}$` : '', true, false);
            datatable.column(1).search(courseFilter ? `^${escapeRegex(courseFilter)}$` : '', true, false);
            datatable.draw();
        });

        table.on('click', '.view-details', function () {
            const button = $(this);
            const rowData = datatable.row(button.closest('tr')).data();
            if (!rowData) {
                return;
            }

            const message = [
                `${rowData[0]} - ${rowData[1]}`,
                `${rowData[2]}`,
                `${rowData[3]}`
            ].filter(Boolean).join('\n');

            window.alert(message);
        });
    }

    $(document).ready(function () {
        const logs = window.controlMinutosLogs || [];
        buildTable(logs);
        initDataTable();
    });
})(jQuery);
