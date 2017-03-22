$(function() {
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
})(jQuery);