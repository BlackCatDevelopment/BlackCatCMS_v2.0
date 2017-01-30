$(function() {

    "use strict";

    var loadPane = function(target,tpl,appendto,data) {
        $.each(data.files, function(index,file) {
            var item = $(tpl).clone();

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
                    src.removeClass('hidden');
                }
                // show mime type
                $(item).find('[data-field="mime_type"]').text(file.mime_type);
            }
            $(item).appendTo(appendto);
        });
    };

    var loadTabContent = function(pane) {
        var get_url = $(pane).attr("data-url");
        if(get_url) {
            var target = $(pane).attr('href');
            switch(target) {
                case "#list":
                    $(target).find('tbody > tr').not('.hidden').remove();
                    var tpl = $(target).find('tbody > tr').clone().detach().removeClass('hidden');
                    var appendto = $(target).find('tbody');
                    break;
                case "#grid":
                    $(target).find('li').not('.hidden').remove();
                    var tpl = $(target).find('li').clone().detach().removeClass('hidden');
                    var appendto = $(target).find('ul.gridder');
                    break;
            }

        	// ajax load from data-url
            $.ajax({
                type    : 'POST',
                url     : get_url,
                dataType: 'json',
                success : function(data, status) {
                    // for debugging
                    //console.log('data: ', data);
                    loadPane(target,tpl,appendto,data);
                }
            });
        }
    };

    // load tab content on activation
    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.preventDefault();
        loadTabContent($(this));
    });
    loadTabContent($('ul[role="tablist"] li.active > a'));

    if(typeof $.fn.Gridder != 'undefined') {
        $('.gridder-table').Gridder({
            expander: "tr.gridder-expander > td",
            rootUrl: CAT_ADMIN_URL+"/media/details",
            gridderContent: "<tr class=\"gridder-show\"><td colspan=\"3\"></td></tr>",
            nextText: "<span class=\"fa fa-fw fa-arrow-right\"></span>",
            prevText: "<span class=\"fa fa-fw fa-arrow-left\"></span>",
            closeText: "<span class=\"fa fa-fw fa-close\"></span>",
            onContent: function(){
                $('div.gridder-show').addClass('panel');
            }
        });
        $('.gridder').Gridder({
            rootUrl: CAT_ADMIN_URL+"/media/details",
            nextText: "<span class=\"fa fa-fw fa-arrow-right\"></span>",
            prevText: "<span class=\"fa fa-fw fa-arrow-left\"></span>",
            closeText: "<span class=\"fa fa-fw fa-close\"></span>",
            onContent: function(){
                $('div.gridder-show').addClass('panel');
            }
        });
    }

    var url          = CAT_ADMIN_URL + '/media/upload',
        uploadButton = $('<button/>')
                     .addClass('btn btn-primary')
                     .prop('disabled', true)
                     .text(cattranslate('Processing...'))
                     .on('click', function () {
                         var $this = $(this),
                              data = $this.data(),
                            folder = $('select#root_folder option:selected').val();
                         data.formData = {folder: folder};
                         $this
                             .off('click')
                             .text('Abort')
                             .on('click', function () {
                                 $this.remove();
                                 data.abort();
                             });
                         data.submit().always(function () {
                             $this.remove();
                         });
                     });

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
        $.each(data.result.success, function(index,filename) {
            data.result.files.push({name: filename, size: : data.result.success[index]});
        });
        $.each(data.result.errors, function(index,filename) {
            data.result.files.push({name: filename, error: data.result.errors[index]});
        });
        $.each(data.result.files, function (index, file) {
            if (file.url) {
                var link = $('<a>')
                    .attr('target', '_blank')
                    .prop('href', file.url);
                $(data.context.children()[index])
                    .wrap(link);
            } else if (file.error) {
                var error = $('<span class="text-danger"/>').text(file.error);
                $(data.context.children()[index])
                    .append('<br>')
                    .append(error);
            }
        });
    }).on('fileuploadfail', function (e, data) {
        $.each(data.files, function (index) {
            var error = $('<span class="text-danger"/>').text(cattranslate('File upload failed.'));
            $(data.context.children()[index])
                .append('<br>')
                .append(error);
        });
    }).prop('disabled', !$.support.fileInput)
        .parent().addClass($.support.fileInput ? undefined : 'disabled');
});