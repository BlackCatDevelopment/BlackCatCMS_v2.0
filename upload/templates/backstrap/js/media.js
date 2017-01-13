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

    // add gridder
    $('.gridder').gridderExpander({
        scroll: true,
        scrollOffset: 30,
        scrollTo: "panel",                  // panel or listitem
        animationSpeed: 400,
        animationEasing: "easeInOutExpo",
        showNav: true,                      // Show Navigation
        nextText: "<span class=\"fa fa-fw fa-arrow-right\"></span>",
        prevText: "<span class=\"fa fa-fw fa-arrow-left\"></span>",
        closeText: "<span class=\"fa fa-fw fa-close\"></span>",
        onContent: function(){
            $('div.gridder-show').addClass('panel');
        }
    });

    $('.gridder-table').gridderExpander({
        scroll: true,
        scrollOffset: 30,
        scrollTo: "panel",                  // panel or listitem
        animationSpeed: 400,
        animationEasing: "easeInOutExpo",
        showNav: true,                      // Show Navigation
        showTemplate: "<tr class=\"gridder-show loading\">",
        outputTemplate: "<td colspan=\"3\" class=\"gridder-padding gridder-expanded-content\">{{shownav}}{{thecontent}}</td></tr>",
        nextText: "<span class=\"fa fa-fw fa-arrow-right\"></span>",
        prevText: "<span class=\"fa fa-fw fa-arrow-left\"></span>",
        closeText: "<span class=\"fa fa-fw fa-close\"></span>",
        onContent: function(){
            $('div.gridder-show').addClass('panel');
        }
    });

    var filtergrid = $('span.bsFilterSelect').clone().detach();

    $(filtergrid)
        .attr('id','filter_grid')
        .removeClass('bsFilterSelect')
        .insertBefore('ul.gridder')
        .show();

    $('select#root_folder').on('change', function() {
        window.location.href = CAT_ADMIN_URL + "/media/index/" + $(this).val().substr(1);
    });

    var curr_path = $('select#root_folder :selected').val();
    if(curr_path.length) {
        $('<div class="pull-right">').html('Verzeichnis: '+curr_path).appendTo('ol.breadcrumb');
    }
});