jQuery(function($) {

    $(".qmx-switch input").on('change',function() {

        var label  = $(this).closest('label'),
            filter = $(this).attr('data-filter'),
            table  = $(this).closest('table'),
            tr     = table.find('tbody tr[data-qm-' + filter + ']'),
            val    = $(this).val();

        var option = table.find('select.qm-filter[data-filter="' + filter + '"] option[value="' + val + '"]');
        var matches = tr.filter('[data-qm-' + filter + '*="' + val + '"]');

        if ($(this).is(':checked')) {
            option.removeClass('qm-hide');
            matches.removeClass('qm-hide');
        } else {
            option.addClass('qm-hide');
            matches.addClass('qm-hide');
        }

        var visible = tr.filter(':visible');

        if ( tr.length === matches.length ) {
			table.find('.qm-items-shown.qm-was-hidden,.qm-items-highlighted.qm-was-hidden').removeClass('qm-was-hidden').addClass('qm-hide');
		} else {
            var results = table.find('.qm-items-shown');
            results.filter('.qm-hide').addClass('qm-was-hidden').removeClass('qm-hide');
            results.find('.qm-items-number').text( QM_i18n.number_format( visible.length, 0 ) );
        }

        if ( visible.length === tr.length )
            results.addClass('qm-hide');

    });

    $("#qm-qmx-included_files table").on('qm-filtered',function(ev,rows) {
        var filesize = 0;
        rows.each(function(row) {
            filesize = filesize + parseInt( $(row).find('td.qmx-includedfiles-filesize').attr('data-qm-sort-weight') );
        });
        $("#qm-qmx-included_files table.qm-sortable tfoot .qm-items-filesize").text(filesize / 1024 + ' KB');
    });

    $("#qm-qmx-included_files .qmx-switch input[value='Plugin: query-monitor']").on('change',function() {
        var qm = $(this);
        var qmx = $(this).closest('.qmx-switch').parent().find('.qmx-switch input[value="Plugin: query-monitor-extend"]');
        if (!qm.is(':checked') && qmx.is(':checked')) {
            qmx.removeAttr('checked');
        } else if (qm.is(':checked') && !qmx.is(':checked'))
            qmx.attr('checked','checked');
    });

});
