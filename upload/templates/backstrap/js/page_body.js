(function(){
    var _MS_PER_DAY = 1000 * 60 * 60 * 24;
    var pageID = $('#bsPageHeader').data('page');

    // a and b are javascript Date objects
    function dateDiffInDays(a, b) {
        // Discard the time and time-zone information.
        var utc1 = Date.UTC(a.getFullYear(), a.getMonth(), a.getDate());
        var utc2 = Date.UTC(b.getFullYear(), b.getMonth(), b.getDate());
        return Math.floor((utc2 - utc1) / _MS_PER_DAY);
    }

    function convertTimestamp(unix_timestamp) {
        var date = new Date(unix_timestamp*1000);
        // Hours part from the timestamp
        var hours = date.getHours();
        // Minutes part from the timestamp
        var minutes = "0" + date.getMinutes();
        // Seconds part from the timestamp
        var seconds = "0" + date.getSeconds();

        // Will display time in 10:30:23 format
        var formattedTime = hours + ':' + minutes.substr(-2) + ':' + seconds.substr(-2);
    }

    // "disable" fuelUX datepicker
    if (!$.fn.bootstrapDP && $.fn.datepicker && $.fn.datepicker.noConflict) {
        var datepicker = $.fn.datepicker.noConflict();
        $.fn.bootstrapDP = datepicker;
    }

    // load tab content on activation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
    	var url     = $(this).attr("data-url");
        if(url)
        {
            e.preventDefault();

          	var pane    = $(this);
            var target  = this.hash;

        	// ajax load from data-url
            $.ajax({
                type    : 'POST',
                url     : url,
                dataType: 'json',
                data    : {
                    page_id: pageID
                },
                success : function(data, status) {
                    if(target == '#headerfiles') {
                        var table      = $('div'+target).find('table');

                        if(data.byplugin) {

                            var thead = $('div'+target).find('thead.pluginname').remove();
                            var tbody = $('div'+target).find('tbody').remove();
                            var tr    = $(tbody).find('tr').remove();

                            $.each(data.byplugin, function(k, v) {
                                var th   = $(thead).clone().detach();
                                var body = $(tbody).clone().detach();
                                th.find('span.pluginname').text(k);
                                th.find('a.plugin_remove').attr('data-plugin',k);
                                for(n=0;n<v.length;n++) {
                                    var line = tr.clone();
                                    line.find('td:nth-of-type(3)').text(v[n]);
                                    line.find('a.plugin_file_remove').attr('data-file',v[n]);
                                    line.appendTo(body);
                                }
                                table.append(th).append(body);
                            });

                            $(table).find('a.plugin_remove').unbind('click').on('click', function(e) {
                                e.preventDefault();
                                var plugin = $(this).data('plugin');
                                $('.modal-body').html(
                                    cattranslate('Do you really want to unlink the selected plugin?',undefined,undefined,'backstrap') +
                                    '<br />' +
                                    plugin
                                );
                                $('.modal-title').text(cattranslate('Remove plugin',undefined,undefined,'backstrap'));
                                $('#modal_dialog').modal('show');
                                $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
                                    e.preventDefault();
                                    $('#modal_dialog').modal('hide');
                                    $.ajax({
                                        type    : 'POST',
                                        url     : CAT_ADMIN_URL + '/page/headerfiles',
                                        dataType: 'json',
                                        data    : {
                                            page_id: pageID,
                                            remove_plugin: plugin
                                        },
                                        success : function(data, status) {
console.log(data);
                                        }
                                    });
                                });
                            });
                            $('a.plugin_file_remove').unbind('click').on('click', function(e) {
                                e.preventDefault();
                                var file = $(this).data('file');
                                $('.modal-body').html(
                                    cattranslate('Do you really want to unlink the selected file?',undefined,undefined,'backstrap') +
                                    '<br />' +
                                    file
                                );
                                //string,elem,attributes,module
                                $('.modal-title').text(cattranslate('Unlink plugin file',undefined,undefined,'backstrap'));
                                $('#modal_dialog').modal('show');
                                $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
                                    e.preventDefault();
                                    $('#modal_dialog').modal('hide');
                                    $.ajax({
                                        type    : 'POST',
                                        url     : CAT_ADMIN_URL + '/page/headerfiles',
                                        dataType: 'json',
                                        data    : {
                                            page_id: pageID,
                                            remove_plugin_file: plugin
                                        },
                                        success : function(data, status) {
console.log(data);
                                        }
                                    });
                                });
                            });
                            $(table).show();
                        }

                        $('div'+target).append(data.forms.be_page_headerfiles_plugin);
                        $('div'+target).append(data.forms.be_page_headerfiles_js);
                        $('div'+target).append(data.forms.be_page_headerfiles_css);

                    }
                    else {
                        $('div'+target).html(data.message);
                    }
                    $('div'+target).find('form').fieldset_to_tabs();
                    $('.fa-spinner').remove();
                    pane.tab('show');
                },
                error   : function(data, status) {
                    pane.find('div').show();
                }
            });
        }
    });

    // drag & drop
    $("ul.draggable-panel").sortable({
        connectWith: "ul.draggable-panel",
        placeholder: "bs_placeholder",
//        forcePlaceholderSize: true,
//        forceHelperSize: true,
        handle: ".fa-arrows",
        axis: "y",
        over:function(event,ui){
            $('.bs_placeholder').parent().addClass('bs_highlight');
        },
        out:function(event,ui){
            $('.bs_placeholder').parent().removeClass('bs_highlight');
        },
        update:function(event,ui){
            // make sure this only fires once
            if (this === ui.item.parent()[0]) {
                $(this).removeClass('bs_highlight');
                $(this).find('.panel').effect("highlight","slow");
                $.ajax({
                    type    : 'POST',
                    url     : CAT_ADMIN_URL + '/section/order',
                    data    : {
                        page_id: pageID,
                        order: $(this).sortable('toArray', {attribute: 'data-id'}),
                    },
                    dataType: 'json'
                });
            }
        }
    });//.disableSelection();

    //
    $.get(CAT_URL+'/backend/languages/select', function(result) {
        $('div#bsLangSelect').html(result.message);
        $('select#language').find('option[value="'+$('#bsPageHeader').data('lang')+'"]').remove();
        $('select#language').on('change', function(e) {
            var lang  = $(this).val();
            var _this = $(this);
            $.ajax({
                type    : 'GET',
                url     : CAT_ADMIN_URL+'/page/list/'+lang,
                dataType: 'json',
                success : function(data, status) {
                    if(data.length)
                    {
                        var $clone  = _this.parent().parent().clone();
                        $clone.find('label').html(cattranslate('Page title'));
                        var $select = $clone.find('select');
                        $select.find('option').remove();
                        $select.attr('name','page').attr('id','page');
                        for(index in data) {
                            var item = data[index];
                            $select.append('<option value="'+item.page_id+'">'+item.menu_title+'</option>');
                        }
                        _this.parent().parent().after($clone);
                    }
                }
            });
        });
    });


    // unlink page
    $('.fa-chain-broken').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('.modal-body').html(
            cattranslate('Do you really want to unlink the selected page?') +
            '<br />' +
            $(this).parent().next('td').next('td').text()
        );
        $('.modal-title').text(cattranslate('Remove relation'));
        $('#modal_dialog').modal('show');
        $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $('#modal_dialog').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/page/unlink',
                dataType: 'json',
                data    : {
                    page_id: pageID,
                    unlink: id
                },
                success : function(data, status) {
                    window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                }
            });
        });
    });

    // add header file
    $('form#be_page_headerfiles').parent().hide();
    var pluginform = $('select#jquery_plugin').parent().parent();
    $('button#bsAddPlugin').unbind('click').on('click', function(e) {
        $('.modal-body').html(pluginform);
        $('.modal-title').text(cattranslate('Add jQuery Plugin'));
        $('#modal_dialog').modal('show');
        $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $('#modal_dialog').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/page/header',
                dataType: 'json',
                data    : {
                    page_id: pageID,
                    jquery_plugin: $('.modal-content select :selected').val()
                },
                success : function(data, status) {
                }
            });
        });
    });

    // delete section
    $('.fa-trash').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('.modal-body').html(
            cattranslate('Do you really want to <strong>delete</strong> this section?') +
            '<br />' +
            cattranslate('ID') + ': ' + id + ' | ' + cattranslate('Module') + ': ' + $(this).data('module')
        );
        $('.modal-title').html('<i class="fa fa-fw fa-warning text-danger"></i> '+cattranslate('Delete section'));
        $('#modal_dialog').modal('show');
        $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/delete/' + id,
                dataType: 'json',
                success : function(data, status) {
                    window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                }
            });
            $('#modal_dialog').modal('hide');
        });
    });

    // recover section
    $('.fa-life-saver').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('.modal-body').html(
            cattranslate('Do you really want to recover this section?')
        );
        $('.modal-title').html('<i class="fa fa-fw fa-life-saver"></i> '+cattranslate('Recover section'));
        $('#modal_dialog').modal('show');
        $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/recover/' + id,
                dataType: 'json',
                success : function(data, status) {
                    window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                }
            });
            $('#modal_dialog').modal('hide');
        });
    });

    // add section
    $('button#bsAddonAdd').unbind('click').on('click', function(e) {
        var module = $('select#module option:selected').val();
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/section/add',
            dataType: 'json',
            data    : {
                module: module,
                block : 1,
                page  : pageID
            },
            success : function(data, status) {
            }
        });
    });

    // toggle visibility
    $('div.panel-heading span.toggle').on('click',function() {
        $(this).parentsUntil('li').next('.panel-body').toggle('slow');
        $(this).toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
    });
    $('div.panel-heading').on('dblclick',function() {
        $(this).next('.panel-body').toggle('slow');
        $(this).find('span.toggle').toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
    });

    // attach publishing date dialog
    $('.fa-calendar').on('click',function(e) {
        var $this = $(this),
            id    = $this.data('id'),
            clone = $('#publishing').clone().detach()
            ;

        $('.modal-body').html(clone.html());
        $('.modal-title').text(cattranslate('Set publishing period',undefined,undefined,'backstrap'));

        //$.datetimepicker.setLocale('de');
        $('.modal-body input.datepicker').datetimepicker({
            defaultTime: '00:00',
            onShow:function(ct,target){
                if($(target).prop('id')=='date_from'){
                    this.setOptions({
                        maxDate: $('input#date_until').val() ? $('input#date_until').val() : false
                    });
                } else {
                    this.setOptions({
                        minDate: $('input#date_from').val() ? $('input#date_from').val() : false
                    });
                }
            }
        });

        $('.modal-body input.timepicker').datetimepicker({
            datepicker:false,
            mask: true,
            format:'H:i'
        });

        // set values
        var dateformat = $('.modal-body input.datepicker').datetimepicker('getFormat');
        if($this.data('pubstart') != 0) {
            var date = new Date($this.data('pubstart')*1000);
            $('.modal-body input#date_from').val($('.modal-body input#date_from').datetimepicker('formatDateTime',date));
        }
        if($this.data('pubend') != 0) {
            var date = new Date($this.data('pubend')*1000);
            $('.modal-body input#date_until').val($('.modal-body input#date_until').datetimepicker('formatDateTime',date));
        }
        if($this.data('timestart') != 0) {
            var date = new Date($this.data('timestart')*1000);
            $('.modal-body input#time_from').val($('.modal-body input#time_from').datetimepicker('formatTime',date));
        }
        if($this.data('timeend') != 0) {
            var date = new Date($this.data('timeend')*1000);
            $('.modal-body input#time_until').val($('.modal-body input#time_until').datetimepicker('formatTime',date));
        }

        $('#modal_dialog').modal('show');

        // note: the unbind() is necessary to prevent multiple execution!
        $('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            var date_from = 0,
                date_until = 0,
                time_from = 0,
                time_until = 0;
            
            if($('.modal-content input#date_from').val() != '') {
                date_from = $('.modal-content input#date_from').val();
            }
            if($('.modal-content input#time_from').val() != '') {
                time_from = $(".modal-content input#time_from").val();
            }
            if($('.modal-content input#date_until').val() != '') {
                date_until = $(".modal-content input#date_until").val();
            }
            if($('.modal-content input#time_until').val() != '') {
                time_until = $(".modal-content input#time_until").val();
            }

            var dates = {
                date_from : date_from,
                time_from : time_from,
                date_until: date_until,
                time_until: time_until,
            };

            $('#modal_dialog').modal('hide');
            if(dates) {
                dates.id = id;
                $.ajax({
                    type    : 'POST',
                    url     : CAT_ADMIN_URL + '/section/publish/' + id,
                    dataType: 'json',
                    data    : dates,
                    success : function(data, status) {
                    }
                });
            }
        });
    });
})(jQuery);