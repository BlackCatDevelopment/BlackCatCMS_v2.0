$(function() {

    /* Custom filtering function which will search data in column four between two values */
    $.fn.dataTable.ext.search.push(
        function(settings,data,dataIndex) {
            var find = $('select#filter option:selected').val();
            if(find == '' || data[0] == find) {
                return true;
            }
            return false;
        }
    );
    // load tab content
    var loadTabContent = function(pane) {
        var get_url = $(pane).attr("data-url");
        if(get_url) {
            var target = $(pane).attr('href');
        	// ajax load from data-url
            $.ajax({
                type    : 'POST',
                url     : get_url,
                dataType: 'json',
                success : function(data, status) {
                    // for debugging
                    console.log('data: ', data);
                    switch(target) {
                        case "#catalog":
                            if(!data.modules || !data.modules.length) {
                                $(target).html('<div class="alert alert-info">No modules</div>');
                            } else {
                                $.each(data.modules, function(index,module) {
                                    var tpl = $('div#bsAddonTemplate table tr:first-of-type').clone().detach();
                                    var item = $(tpl).clone().detach().html();
                                    var version = module.installed_version;
                                    if(typeof version == 'undefined') {
                                        version = '';
                                    }
                                    item = item.replace(/%%type%%/g,module.type)
                                               .replace(/%%name%%/g,module.name)
                                               .replace(/%%description%%/g,module.description.en.title)
                                               .replace(/%%author%%/g,module.author)
                                               .replace(/%%installedversion%%/g,version)
                                               .replace(/%%version%%/g,module.version)
                                               .replace(/%%license%%/g,module.license);
                                    $(tpl).html(item);
                                    if(module.is_installed) {
                                        $(tpl).find('.btn-success').remove();
                                        $(tpl).find('td.bs-module-installed span').addClass('fa-check');
                                    } else {
                                        $(tpl).find('.btn-danger').remove();
                                    }
                                    if(!module.upgradable) {
                                        $(tpl).find('.btn-info').remove();
                                    }
                                    $(tpl).attr('class','type_'+module.type);
                                    $(tpl).appendTo($('div#catalog table tbody'));
                                });
                                if($.fn.dataTable.isDataTable('table#bsCatalog')) {
                                    cattable = $('table#bsCatalog').DataTable();
                                } else {
                                    cattable = $('table#bsCatalog').DataTable({
                                        orderClasses: false,
                                        language: {
                                            url: CAT_ASSET_URL
                                        }
                                    });
                                }
                                // funktioniert noch nicht
                                $('select#filter').on('change', function () {
                                    cattable.draw();
                                });
                            }
                            break;
                    }
                }
            });
        }
    };
    $('select#filter').on('change', function () {
        dtable.draw();
    });
    // load tab content on activation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.preventDefault();
        loadTabContent($(this));
    });
});