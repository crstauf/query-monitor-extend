jQuery(function($) {

    $(".qmx-filter-hide input").on('change',function() {

        var label  = $(this).closest('label'),
            filter = $(this).attr('data-filter'),
            table  = $(this).closest('table'),
            tr     = table.find('tbody tr[data-qm-' + filter + ']'),
            val    = $(this).val();

        table.find('select.qm-filter[data-filter="' + filter + '"] option[value="' + val + '"]').toggleClass('qm-hide');
        tr.filter('[data-qm-' + filter + '*="' + val + '"]').toggleClass('qm-hide');

        var matches = tr.filter(':visible');

        if ( tr.length === matches.length ) {
			table.find('.qm-items-shown.qm-was-hidden,.qm-items-highlighted.qm-was-hidden').removeClass('qm-was-hidden').addClass('qm-hide');
		} else {
            var results = table.find('.qm-items-shown');
            results.filter('.qm-hide').addClass('qm-was-hidden').removeClass('qm-hide');
            results.find('.qm-items-number').text( QM_i18n.number_format( matches.length, 0 ) );
        }

        $(this).blur();

    });

    $("#qm-included_files table").on('qm-filtered',function(ev,rows) {
        var filesize = 0;
        rows.each(function(row) {
            filesize = filesize + parseInt( $(row).find('td.qmx-includedfiles-filesize').attr('data-qm-sort-value') );
        });
        $("#qm-includedfiles table.qm-sortable tfoot .qm-items-filesize").text(filesize / 1024 + ' KB');
    });

});
