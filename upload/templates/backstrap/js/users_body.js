$(function() {
    var dtable = $('table.dtable').DataTable({
        mark: true,
        stateSave: true,
        orderClasses: false,
        language: {
            url: CAT_ASSET_URL
        }
    });

    $("a.delete").unbind("click").on("click",function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        $(".modal-body").html(
            cattranslate("Do you really want to delete this user?") +
            "<br />" +
            $(this).data("name")
        );
        $(".modal-title").text(cattranslate("Delete user",undefined,undefined,"BE"));
        $("#modal_dialog").modal("show");
        $(".modal-content button.btn-primary").unbind("click").on("click",function(e) {
            e.preventDefault();
            $("#modal_dialog").modal("hide");
            $.ajax({
                type    : "GET",
                url     : CAT_ADMIN_URL+"/users/delete/"+id,
                dataType: "json",
                success : function(data, status) {
                    BCGrowl(data.message,data.success);
                }
            });
        });
    });

    $("a.bsedit").unbind("click").on("click",function(e) {
        e.preventDefault();
        var id = $(this).data("id");
        $.ajax({
                type    : "POST",
                url     : CAT_ADMIN_URL+"/users/edit",
                dataType: "json",
                data    : {
                    user_id: id
                },
                success : function(data, status) {
                    $(".modal-title").text(cattranslate("Edit user",undefined,undefined,"BE"));
                    $(".modal-body").html(data.form);
                    $('form').fieldset_to_tabs();
                    $('div.fbbuttonline').remove();
                    $('div.form-group').addClass('row');
                    $('form').find('br').remove();
                    $("#modal_dialog").modal("show");
                    //BCGrowl(data.message,data.success);
                }
            });
    });
});