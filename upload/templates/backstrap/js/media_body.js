$(function() {
    "use strict";

    // extract the templates
    var tablerow = $(this).find('tbody > tr').clone().detach().removeAttr('hidden');
    var listitem = $(this).find('li.gridder-expander').clone().detach().removeAttr('hidden');

    var loadPane = function(target,data) {
        var panelid = $(target).attr('id');
        switch(panelid) {
            case "list":
                $(target).find('tbody > tr').not(':hidden').remove();
                var appendto = $(target).find('tbody');
                var tables = $('table.datatable').DataTable();
                tables.table(0).clear().draw();
                break;
            case "grid":
                $(target).find('li').not(':hidden').remove();
                var appendto = $(target).find('ul.gridder');
                var toclone  = listitem;
                break;
        }

        if(!data.files || !data.files.length) {
            //$(target).html('<div class="alert alert-info">No files</div>');
        } else {

            $.each(data.files, function(index,file) {
                var item = $(toclone).clone();

                $(item).attr("data-gridder-url",file.media_id);
                $(item).attr("data-title",file.filename);

                $(item).find('a.delete').attr('data-id',file.media_id);
                $(item).find('[data-field="filename"]').text(file.filename);
                $(item).find('[data-field="hfilesize"]').text(file.hfilesize);
                $(item).find('[data-field="moddate"]').text(file.moddate);

                if(file.mime_type) {
                    // show preview
                    if(file.mime_type.indexOf('image/') == 0) {
                        var src = $(item).find('[data-field="preview"]');
                        src.attr('src',src.attr('src')+file.url);
                    } else {
                        $(item).find('[data-field="preview"]').remove();
                        $(item).find('.fa-file-movie-o').removeAttr('hidden');
                    }
                    // show mime type
                    $(item).find('[data-field="mime_type"]').text(file.mime_type);
                }

                $(item).removeAttr('hidden');

                switch(panelid) {
                    case "list":
                        tables.table(0).rows.add($(item)).draw();
                        break;
                    case "grid":
                        $(item).appendTo(appendto);
                        break;
                    }
            });
        }
    };

    // load tab content on activation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        var get_url = $(this).attr("data-url");
        if(get_url) {
            e.preventDefault();
          	var pane    = $(this);
            var target  = this.hash;
            var row     = false;
            var parent  = false;
            var count   = {
                '#list': 0,
                '#grid': 0
            };

            switch(target) {
                case '#list':
                    parent = $('table.table');
                    row = $(tablerow).clone();
                    count[target] = $('tbody').find('tr').length;
                    break;
                case '#grid':
                    parent = $('ul.gridder');
                    row = $(listitem).clone();
                    count[target] = $(parent).find('li').length;
                    break;
            }

            // only on first load
            if(count[target]<=1) {
            	// ajax load from data-url
                $.ajax({
                    type    : 'POST',
                    url     : get_url,
                    dataType: 'json',
                    success : function(data, status) {
                        // console.log('data: ', data);
                        // reset folder select
                        var s = $('select#root_folder');
                        s.find('option').each(function() {
                            if($(this).val() != '') {
                                $(this).remove();
                            }
                        });
                        // fill the folder select
                        if(data.dirs) {
                            $.each(data.dirs, function(index,dir) {
                                s.append('<option value="'+dir+'">'+dir+'</option>');
                            });
                        }
                        if(data.files) {
                            $.each(data.files, function(index,file) {
                                row = $(row).clone();
                                $('a.delete',row).attr("data-id",file.media_id);
                                if(file.mime_type) {
                                    // show preview
                                    if(file.mime_type.indexOf('image/') == 0) {
                                        $('img.thumb',row).attr("src",data.media_url + file.url).removeAttr('hidden');
                                    }
                                }
                                $('span.filename',row).text(file.filename);
                                $('span.filetype',row).text(file.mime_type);
                                $('span.hfilesize',row).text(file.hfilesize);
                                $('span.moddate',row).text(file.moddate);
                                $(row).appendTo($(parent));
                            });
                        }
                        $('table').DataTable();
                    }
                });
            }
        }
    });

    // select which tab to show (last viewed or default)
    var lastTab = localStorage.getItem('lastTab');
    if(!lastTab) {
        lastTab = '#list';
    }
    $('[href="' + lastTab + '"]').trigger('click');


    // #########################################################################
    // handle folder select
    // #########################################################################
    $('select#root_folder').on('change', function() {
        if($('.nav-tabs li a.active').attr('aria-controls') !== 'upload') {
            // get the files
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL+"/media/index/"+$(this).val(),
                dataType: 'json',
                success : function(data, status) {
                    // for debugging
                    //console.log('data: ', data);
                    loadPane($('div.tab-pane.active'),data);
                }
            });
        }
    });

    // #########################################################################
    // handle file upload
    // #########################################################################
    $('#fileupload').fileupload({
        url: url,
        dataType: 'json',
        autoUpload: false,
        acceptFileTypes: /(\.|\/)(gif|jpe?g|png)$/i,
        maxFileSize: 999000,
        // Enable image resizing, except for Android and Opera,
        // which actually support image resizing, but fail to
        // send Blob objects via XHR requests:
        disableImageResize: /Android(?!.*Chrome)|Opera/
            .test(window.navigator.userAgent),
        previewMaxWidth: 100,
        previewMaxHeight: 100,
        previewCrop: true
    }).on('fileuploadadd', function (e, data) {
        data.context = $('<tr/>').appendTo('table#bsUploadFiles > tbody');
        $.each(data.files, function (index, file) {
            $('<td/>').appendTo(data.context);
            $('<td/>').text(file.name).appendTo(data.context);
            $('<td style="width:150px"/>').html('<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>').appendTo(data.context);
            $('<td/>').text(file.type).appendTo(data.context);
            $('<td/>').html(uploadButton.clone(true).data(data)).appendTo(data.context);
        });
        $('button.start').removeClass('disabled');
        $('button.delete').removeClass('disabled');
        $('div#progress').removeAttr('hidden');
        $('#bsUploadFiles > thead').show();
    }).on('fileuploadprocessalways', function (e, data) {
        var index = data.index,
            file  = data.files[index],
            node  = $(data.context.children()[index]);

        if (file.preview) {
            node.html(file.preview);
        }
        if (file.error) {
            node
                .append('<br>')
                .append($('<span class="text-danger"/>').text(file.error));
        }
        if (index + 1 === data.files.length) {
            data.context.find('button')
                .text('Upload')
                .prop('disabled', !!data.files.error);
        }
    }).on('fileuploadprogressall', function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#progress .progress-bar').css(
            'width',
            progress + '%'
        );
    }).on('fileuploaddone', function (e, data) {
        data.result.files = new Array();
        // we get 'ok' and 'errors' arrays as result, so we have to prepare the
        // result for the file upload jQuery plugin
        // {"success":{"br_1.jpg":84967},"errors":[]}
        $.each(data.result.success, function(filename,size) {
            data.result.files.push({name: filename, size: size, success: true}); //, size: : data.result.success[index]
        });
        $.each(data.result.errors, function(index,filename) {
            data.result.files.push({name: filename, error: data.result.errors[index]});
        });
        $.each(data.result.files, function (index, file) {
            if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
            else if (file.success) {
                $(data.context.children()[index])
                    .append('<br>')
                    .append($('<span class="alert alert-info"/>').text(
                        $.cattranslate('Success, file size: '+file.size)
                    ));
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text($.cattranslate('File upload failed.'));
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});