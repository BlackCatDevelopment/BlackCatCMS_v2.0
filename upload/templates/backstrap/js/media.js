$(function() {
    var filtergrid = $('span.bsFilterSelect').clone().detach();

    $(filtergrid)
        .attr('id','filter_grid')
        .removeClass('bsFilterSelect')
        .insertBefore('ul.gridder')
        .show();
});