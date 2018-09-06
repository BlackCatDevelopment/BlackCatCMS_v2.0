$(function() {
    $('select#filter').on('change', function () {
        var filter = $(this).val();
        filter = filter.substring(0, filter.length - 1);
        $("table.table tbody tr").show();
        if(filter.length>0) {
            $("table.table tbody tr:not('.type_"+filter+"')").hide();
        }
    });
});