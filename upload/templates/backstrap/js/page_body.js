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

    function bsChangeLangSelect() {
        count = $('select#linked_page option[data-lang="' + $('select#relation_lang').val() + '"]').length;
        if(count==0) {
            $('select#linked_page').parent().parent().hide();
            $('button#bsSaveLangRelation').hide();
            $('#bsNoPagesInfo').show();
        } else {
            $('select#linked_page').parent().parent().show();
            $('button#bsSaveLangRelation').show();
            $('#bsNoPagesInfo').hide();
        }
        $('select#linked_page option[data-lang!="' + $('select#relation_lang').val() + '"]').attr('disabled','disabled');
    }

    // get the time period settings template
    var bsPublishingTemplate = $('#publishing').detach();
    var bsModalTemplate      = $('#bsDialog').clone().detach();

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
/*
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
                                    $.cattranslate('Do you really want to unlink the selected plugin?',undefined,undefined,'backstrap') +
                                    '<br />' +
                                    plugin
                                );
                                $('.modal-title').text($.cattranslate('Remove plugin',undefined,undefined,'backstrap'));
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
                                    $.cattranslate('Do you really want to unlink the selected file?',undefined,undefined,'backstrap') +
                                    '<br />' +
                                    file
                                );
                                //string,elem,attributes,module
                                $('.modal-title').text($.cattranslate('Unlink plugin file',undefined,undefined,'backstrap'));
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
*/
                    }
                    else {
                        $('div'+target).html(data.message);
                    }
                    //$('div'+target).find('form').fieldset_to_tabs();
                    $('.fa-spinner').remove();
                    $('div#headerfiles').html(data.content);
                    pane.tab('show');
                },
                error   : function(data, status) {
                    pane.find('div').show();
                }
            });
        }
    });

    // unhide buttons
    if($("ul.draggable-card > li").length>0) {
        $("#bsCollapseAll").removeAttr('hidden');
        $("#bsExpandAll").removeAttr('hidden');
        // drag & drop
        if($("ul.draggable-card > li").length>1) {
            $("ul.draggable-card").sortable({
                connectWith: "ul.draggable-card",
                placeholder: "bs_placeholder",
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
                        $(this).find('.card').effect("highlight","slow");
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
        } else {
            $("span.fa-arrows").hide();
        }
    }

    // language relations
    $('select#relation_lang').on('focus,change', function() {
        bsChangeLangSelect();
    });
    bsChangeLangSelect(); // initial

    $('button#bsSaveLangRelation').on('click', function(e) {
        e.preventDefault();
        if($('select#linked_page option:selected').val()=="") {
            BCGrowl($.cattranslate('Please select a page to link to'));
        } else {
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/page/save',
                dataType: 'json',
                data    : $('form#bsAddPageRelation').serialize(),
                success : function(data, status) {
                    if(data.success==true) {
                        BCGrowl($.cattranslate('Success'),true);
                    } else {
                        BCGrowl(data.message);
                    }
                }
            });
        }
    });

    // ----- remove page relation ----------------------------------------------
    $('.fa-chain-broken').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        var _this = $(this);
        $('#bsDialog .modal-body').html(
            $.cattranslate('Do you really want to unlink the selected page?') +
            '<br />' +
            $(this).parent().next('td').next('td').text()
        );
        $('#bsDialog .modal-title').text($.cattranslate('Remove relation'));
        $('#bsDialog').modal('show');
        $('#bsDialog .modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $('#bsDialog').modal('hide');
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/page/unlink',
                dataType: 'json',
                data    : {
                    page_id: pageID,
                    unlink: id
                },
                success : function(data, status) {
                    _this.parent().parent().remove();
                    BCGrowl($.cattranslate('Success'),true);
                }
            });
        });
    });

    // ----- add header file ---------------------------------------------------
    $('form#be_page_headerfiles').parent().hide();
    var pluginform = $('select#jquery_plugin').parent().parent();
    $('button#bsAddPlugin').unbind('click').on('click', function(e) {
        $('.modal-body').html(pluginform);
        $('.modal-title').text($.cattranslate('Add jQuery Plugin'));
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

    // ----- show options panel ------------------------------------------------
    $('.fa.fa-cogs').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $(this).parent().toggleClass('bg-light');
        $('#bsOptionsPanel_'+id).toggle('slow');
    });

    $('div.card-content div.form-group.row.buttonline input.btn.btn-primary').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('#bsOptionsPanel_'+id).hide('slow');
    });

    // ----- delete section ----------------------------------------------------
    $('.fa-trash').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('#bsDialog .modal-body').html(
            $.cattranslate('Do you really want to delete this section?',undefined,undefined,'BE') +
            '<br />' +
            $.cattranslate('ID') + ': ' + id + ' | ' + $.cattranslate('Module',undefined,undefined,'BE') + ': ' + $(this).data('module')
        );
        $('#bsDialog .modal-title').html('<i class="fa fa-fw fa-warning text-danger"></i> '+$.cattranslate('Delete section'));
        $('#bsDialog ').modal('show');
        $('#bsDialog .modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/delete',
                dataType: 'json',
                data    : {
                    page_id   : pageID,
                    section_id: id
                },
                success : function(data, status) {
                    BCGrowl($.cattranslate(data.message),true);
                    if(data.success) {
                        window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                    }
                }
            });
            $('#modal_dialog').modal('hide');
        });
    });

    // ----- recover section ---------------------------------------------------
    $('.fa-life-saver').unbind('click').on('click', function(e) {
        var id = $(this).data('id');
        $('#bsDialog .modal-body').html(
            $.cattranslate('Do you really want to recover this section?')
        );
        $('#bsDialog .modal-title').html('<i class="fa fa-fw fa-life-saver"></i> '+$.cattranslate('Recover section'));
        $('#bsDialog').modal('show');
        $('#bsDialog .modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/recover/' + id,
                dataType: 'json',
                success : function(data, status) {
                    BCGrowl($.cattranslate('Success'),true);
                    if(data.success) {
                        window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                    }
                }
            });
            $('#bsDialog').modal('hide');
        });
    });

    // ----- add section -------------------------------------------------------
    $('button#bsAddonAdd').unbind('click').on('click', function(e) {
        var addon = $('select#module option:selected').val();
        if(addon.length) {
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/add',
                dataType: 'json',
                data    : {
                    addon  : addon,
                    block  : 1,
                    page_id: pageID
                },
                success : function(data, status) {
                    BCGrowl($.cattranslate(data.message),true);
                    if(data.success) {
                        window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                    }
                }
            });
        }
    });

    // ----- move section ------------------------------------------------------
    $('.fa-external-link').unbind('click').on('click', function(e) {
        var dialog = $('#bsDialog').clone().detach();
        var id     = $(this).data('id');
        $(dialog).find('.modal-title').text($.cattranslate('Move section to another page'));
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/page/list',
            dataType: 'json',
            success : function(data, status) {
                var select = $('<select name="page" id="page">');
                var prefix = "|- ";
                for(index in data) {
                    var offset = prefix.repeat(data[index].level);
                    select.append('<option value="'+data[index].page_id+'"'+(data[index].page_id==pageID ? ' disabled="disabled"' : '')+'>'+offset+data[index].menu_title+'</option>');
                }
                select.appendTo($(dialog).find('.modal-body'));
                $(dialog).modal('show');
                $(dialog).find('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
                    e.preventDefault();
                    var to = $(dialog).find('.modal-content select :selected').val();
                    $(dialog).modal('hide');
                    $.ajax({
                        type    : 'POST',
                        url     : CAT_ADMIN_URL + '/section/move',
                        dataType: 'json',
                        data    : {
                            page_id: pageID,
                            section_id: id,
                            to: to
                        },
                        success : function(data, status) {
                            if(data.success) {
                                if(data.message) {
                                    BCGrowl($.cattranslate(data.message));
                                }
                                window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID
                            }
                        }
                    });
                });
            }
        });
        
    });

    // ----- toggle visibility -------------------------------------------------
    $('div.card-header span.toggle').on('click',function() {
        $(this).parentsUntil('li').next('.card-body').toggle('slow');
        $(this).toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
    });
    $('div.card-header').on('dblclick',function() {
        $(this).next('.card-body').toggle('slow');
        $(this).find('span.toggle').toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
    });
    $('button#bsCollapseAll').on('click',function(e) {
        e.preventDefault();
        $('ul.draggable-card li.card').each(function() {
            $(this).find('.card-body').hide();
            $(this).find('span.toggle').toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
        });
    });
    $('button#bsExpandAll').on('click',function(e) {
        e.preventDefault();
        $('ul.draggable-card li.card').each(function() {
            $(this).find('.card-body').show();
            $(this).find('span.toggle').toggleClass('fa-chevron-down').toggleClass('fa-chevron-right');
        });
    });

    // ----- attach publishing date/time dialog --------------------------------
    $('.fa-calendar').on('click',function(e) {

        var $this = $(this),
            id    = $this.data('id'),
            clone = bsPublishingTemplate.clone().detach(),
            modal = bsModalTemplate.clone()
            ;

        $(modal).find('.modal-body').html(clone.html());
        $(modal).find('.modal-title').text($.cattranslate('Set publishing period',undefined,undefined,'backstrap'));

        //$.datetimepicker.setLocale('de');
        $(modal).find('.modal-body input.datepicker').datetimepicker({
            defaultTime: '00:00',
            onShow:function(ct,target){
                if($(target).prop('id')=='publ_start'){
                    this.setOptions({
                        maxDate: $('input#publ_end').val() ? $('input#publ_end').val() : false
                    });
                } else {
                    this.setOptions({
                        minDate: $('input#publ_start').val() ? $('input#publ_start').val() : false
                    });
                }
            }
        });

        $(modal).find('.modal-body input.timepicker').datetimepicker({
            datepicker: false,
            mask      : true,
            format    :'H:i'
        });

        // set values
        var dateformat = $(modal).find('.modal-body input.datepicker').datetimepicker('getFormat');
        if($this.attr('data-pubstart') != 0) {
            var date = new Date($this.attr('data-pubstart')*1000);
            $(modal)
                .find('.modal-body input#publ_start')
                .val($(modal).find('.modal-body input#publ_start').datetimepicker('formatDateTime',date));
        }
        if($this.attr('data-pubend') != 0) {
            var date = new Date($this.attr('data-pubend')*1000);
            $(modal)
                .find('.modal-body input#publ_end')
                .val($(modal).find('.modal-body input#publ_end').datetimepicker('formatDateTime',date));
        }
        if($this.attr('data-timestart') != 0) {
            var date = new Date($this.attr('data-timestart')*1000);
            $(modal)
                .find('.modal-body input#publ_by_time_start')
                .val($(modal).find('.modal-body input#publ_by_time_start').datetimepicker('formatTime',date));
        }
        if($this.attr('data-timeend') != 0) {
            var date = new Date($this.attr('data-timeend')*1000);
            $(modal)
                .find('.modal-body input#publ_by_time_end')
                .val($(modal).find('.modal-body input#publ_by_time_end').datetimepicker('formatTime',date));
        }

        $(modal).find('.fa-trash').unbind('click').on('click',function() {
            $(this).prev('input').val('');
        });

        $(modal).modal('show');

        // note: the unbind() is necessary to prevent multiple execution!
        $(modal).find('.modal-content button.btn-primary').unbind('click').on('click',function(e) {
            e.preventDefault();
            var publ_start = 0,
                publ_end = 0,
                publ_by_time_start = 0,
                publ_by_time_end = 0;

            // start end end date
            if($(modal).find('.modal-content input#publ_start').val() != '') {
                publ_start = $('.modal-content input#publ_start').val();
            }
            if($(modal).find('.modal-content input#publ_end').val() != '') {
                publ_end = $(".modal-content input#publ_end").val();
            }

            // start and end time (per day)
            if(
                   $(modal).find('.modal-content input#publ_by_time_start').val() != ''
                && $(modal).find('.modal-content input#publ_by_time_start').val() != '__:__'
            ) {
                publ_by_time_start = $(".modal-content input#publ_by_time_start").val();
            }
            if(
                   $(modal).find('.modal-content input#publ_by_time_end').val() != ''
                && $(modal).find('.modal-content input#publ_by_time_end').val() != '__:__'
            ) {
                publ_by_time_end = $(".modal-content input#publ_by_time_end").val();
            }

            var dates = {
                publ_start        : publ_start,
                publ_by_time_start: publ_by_time_start,
                publ_end          : publ_end,
                publ_by_time_end  : publ_by_time_end
            };

            $(modal).modal('hide');
            $('.xdsoft_datetimepicker').remove();

            if(dates) {
                dates.section_id = id;
                $.ajax({
                    type    : 'POST',
                    url     : CAT_ADMIN_URL + '/section/publish/' + id,
                    dataType: 'json',
                    data    : dates,
                    success : function(data, status) {
                        $('span.fa-calendar[data-id="'+id+'"]')
                            .attr('data-pubstart',data.publ_start)
                            .attr('data-pubend',data.publ_end)
                            .attr('data-timestart',data.publ_by_time_start)
                            .attr('data-timeend',data.publ_by_time_end);
                        BCGrowl($.cattranslate('Successfully saved'),true);
                    }
                });
            }
        });
    });

    // ----- select variant ----------------------------------------------------
    $('input.bsVariantSave').unbind('click').on('click',function(e) {
        var _this   = this;
        var id      = $(this).data('id');
        var variant = $(this).parent().parent().find('select[name=variant]').val();
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/section/save/' + id,
            dataType: 'json',
            data    : { variant: variant},
            success : function(data, status) {
                window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID;
            }
        });
    });

    // ----- save settings -----------------------------------------------------
    $('div#contents.tab-pane.active div.options-panel form input.btn.btn-primary').unbind('click').on('click', function(e) {
        e.preventDefault();
        var section_id = $('div#contents.tab-pane.active div.options-panel form input[name=section_id]').val();
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL + '/section/save/' + section_id,
            data    : $('div#contents.tab-pane.active div.options-panel form').serialize(),
            dataType: 'json',
            success : function(data, status) {
                if(data.success) {
                    BCGrowl($.cattranslate(data.message),data.success);
                    window.location.href = CAT_ADMIN_URL + '/page/edit/' + pageID;
                } else {
                    BCGrowl($.cattranslate(data.message));
                }
            }
        });
    });

    // ----- save content(s) ---------------------------------------------------
    $('div#contents.tab-pane.active button.btn.btn-primary.btn-save').unbind('click').on('click', function() {
        var _this      = this;
        var section_id = $(this).data('id');
        var page_id    = $(this).data('page');
        var card       = $(this).parent().parent();
        var data       = {
            contents: new Array(),
            page_id: page_id,
            section_id: section_id
        };

        // inline editor
        $(card).find("[contenteditable].haschanged").each(function() {
            var col     = $(this).data("col");
            var opt     = $(this).data("option");
            var content = $(this).html();
            data.contents.push({attribute:opt,column:col,content:content});
            $(this).removeClass("haschanged");
        });

        // default WYSIWYG section
        $(card).find('textarea.wysiwyg').each(function() {
            var instance = $(this).attr('name');
            data.contents.push({content:CKEDITOR.instances[instance].getData()});
        });

        if(data.contents.length==0) {
            BCGrowl($.cattranslate("No data to send"));
        } else {

console.log(data);

            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/section/save/' + section_id,
                data    : data,
                dataType: 'json',
                success : function(data, status) {
                    BCGrowl($.cattranslate(data.message),data.success);
                    if(data.success) {
                        $(card).parent().find('.bsChangedFlag').removeClass("fa-exclamation-triangle").removeClass("text-warning");
                        if(!$("[contenteditable].haschanged").length) {
                            $('span#bsGlobalChangeIndicator').removeClass("fa-exclamation-triangle").removeClass("text-warning");
                        }
                    } else {
                        $(card).parent().find('.bsChangedFlag').removeClass("text-warning").addClass("text-danger");
                    }
                }
            });
        }
    });

    // allows to click on the select inside the dropdown without closing it
    $('.keep-open').on('click', function(e) {
        e.stopPropagation(); 
    });

    $('button#bsAddCSS').unbind('click').on('click',function(e) {
        var list = $('div#bsCSSFiles').clone();
        $('.modal-body').html(list);
        //$('.modal-title').text($.cattranslate('Remove plugin',undefined,undefined,'backstrap'));
        $('#tplcss').modal('show');
    });

    $("[contenteditable]").each(function () {
        var target = this;
        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === "class" && $(mutation.target).prop(mutation.attributeName).indexOf("haschanged")>0) {
                    var attributeValue = $(mutation.target).prop(mutation.attributeName);
                    //console.log("Class attribute changed to:", attributeValue);
                    $(mutation.target).parentsUntil('li.card').parent().find('.bsChangedFlag').addClass("fa-exclamation-triangle text-warning");
                    $('span#bsGlobalChangeIndicator').addClass("fa-exclamation-triangle text-warning");
                }
            });
        });
        observer.observe(target, {attributes: true});
    });

})(jQuery);