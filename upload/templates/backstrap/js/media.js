$(function() {

    // save last selected tab on toggle
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        localStorage.setItem('lastTab', $(this).attr('href'));
    });

    // select which tab to show (last viewed or default)
    var lastTab = localStorage.getItem('lastTab');
    if(!lastTab) {
        lastTab = 'list';
    }
    $('[href="' + lastTab + '"]').tab('show');

    var filtergrid = $('span.bsFilterSelect').clone().detach();

    $(filtergrid)
        .attr('id','filter_grid')
        .removeClass('bsFilterSelect')
        .insertBefore('ul.gridder')
        .show();

    $('select#root_folder').on('change', function() {
        if(!$('.nav-tabs .active').attr('id') == 'upload') {
            window.location.href = CAT_ADMIN_URL + "/media/index/" + $(this).val().substr(1);
        }
    });

    var curr_path = $('select#root_folder :selected').val();
    if(curr_path.length) {
        $('<div class="pull-right">').html('Verzeichnis: '+curr_path).appendTo('ol.breadcrumb');
    }
});