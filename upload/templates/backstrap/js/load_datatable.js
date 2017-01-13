$(function() {
    var dtfilter = $('span.bsFilterSelect').clone().detach();
    var dtable   = $('table.datatable').DataTable({
        mark: true,
        //stateSave: true,
        order: [[1, 'asc']],
        columnDefs: [
            { orderable: false, targets: 0 }
        ],
        orderClasses: false,
        language: {
            url: CAT_ASSET_URL
        },
        initComplete: function () {
            $(dtfilter)
                .removeClass('bsFilterSelect')
                .attr('id','filter_list')
                .appendTo('div#DataTables_Table_0_filter')
                .show();
            $('span#filter_list select').on('change', function() {
                var regExSearch = '^.*'+$.fn.dataTable.util.escapeRegex(
                    $(this).val()+'/'
                )+'.*$';
                dtable.column(2)
                      .search(regExSearch, true, false)
                      .draw();
            });
        }
    });
});