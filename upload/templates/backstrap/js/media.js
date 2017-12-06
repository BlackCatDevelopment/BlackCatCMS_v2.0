$(function() {
    // save last selected tab on toggle
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('lastTab', $(this).attr('href'));
        $('select#root_folder option[value=""]').prop('selected',true);
    });

    var filtergrid = $('span.bsFilterSelect').clone().detach();

    $(filtergrid)
        .attr('id','filter_grid')
        .removeClass('bsFilterSelect')
        .insertBefore('ul.gridder')
        .show();

    var curr_path = $('select#root_folder :selected').val();
    if(curr_path.length) {
        $('<div class="pull-right">').html('Verzeichnis: '+curr_path).appendTo('ol.breadcrumb');
    }
});