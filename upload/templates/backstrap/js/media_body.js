$(function() {
    "use strict";

    // #########################################################################
    // handle folder delete
    // #########################################################################
    $('a.folder-delete').unbind('click').on('click', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $("#bsDialog .modal-body").html(
            $.cattranslate("Do you really want to delete this folder and all it's subfolders and files?") +
            "<br /><i class=\"fa fa-fw fa-long-arrow-right\"></i>" +
            $(this).data("name")
        );
        $("#bsDialog .modal-title").text($.cattranslate("Delete folder"));
        $("#bsDialog").modal("show");
        $("#bsDialog .modal-content button.btn-primary").unbind("click").on("click",function(e) {
            e.preventDefault();
            $("#bsDialog").modal("hide");
            $.ajax({
                type    : "GET",
                url     : CAT_ADMIN_URL+"/media/delete/"+id,
                dataType: "json",
                success : function(data, status) {
                    BCGrowl(data.message,data.success);
                }
            });
        });
    });

    // #########################################################################
    // handle folder select
    // #########################################################################
    $('select#dir_id').on('change', function() {
console.log("jo");
        $('form#bsMediaFolderSelect').submit();
    });

    // #########################################################################
    // lightbox for files
    // #########################################################################
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox();
    });

    // #########################################################################
    // handle file upload
    // #########################################################################

   // Initialize the jQuery File Upload widget:
    $('#fileupload').fileupload({
        // Uncomment the following to send cross-domain cookies:
        //xhrFields: {withCredentials: true},
        dataType: 'json',
        autoUpload: false

    }).on('fileuploadadd', function (e, data) {
        data.context = $('<tr/>').appendTo('table#bsUploadFiles > tbody');
        $.each(data.files, function (index, file) {
            $('<td/>').appendTo(data.context);
            $('<td/>').text(file.name).appendTo(data.context);
            $('<td style="width:150px"/>').html('<div class="progress progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0"><div class="progress-bar progress-bar-success" style="width:0%;"></div></div>').appendTo(data.context);
            $('<td/>').text(file.type).appendTo(data.context);
            $('<td/>').html('<button class="btn btn-primary start"><i class="fa fa-upload"></i> Start</button> <button class="btn btn-warning cancel"><i class="fa fa-ban"></i> Cancel</button>').appendTo(data.context);
        });
        $('button.start').removeClass('disabled');
        $('button.delete').removeClass('disabled');
        $('div#progress').removeAttr('hidden');
        $('#bsUploadFiles > thead').show();
    });



});