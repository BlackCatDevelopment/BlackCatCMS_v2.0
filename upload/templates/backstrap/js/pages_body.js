$(function() {

    calcWidth($(".treecontainer > ul"));

    window.onresize = function(event) {
        //console.log("window resized");
        fullwidth = $(".treecontainer").width();
        container_offset = $(".treecontainer").offset();
        calcWidth($(".treecontainer > ul"));
    };

    function calcWidth(obj){
        //console.log("---- calcWidth -----");
        var fullwidth = $(".treecontainer").width();
        var container_offset = $(".treecontainer").offset();
        $(obj).find("div.panel").each(function() {
            var position = $(this).offset();
            var newWidth = fullwidth - position.left + container_offset.left;
            $(this).css("width",newWidth);
        });
    }

    $(".space").sortable({
        connectWith:".space",
        tolerance:"intersect",
        over:function(event,ui){ },
        receive:function(event, ui){
            //var position = $(ui.item).offset();
            //var newWidth = fullwidth - position.left + container_offset.left + 5;
            //$(ui.item).find("div.panel").css("width",newWidth);
            calcWidth($(".treecontainer > ul"));
        }
    });

    $(".space").disableSelection();

    $(".bs-page-visibility").editable({
        send: "never",
        placement: "bottom",
        display: function(value) {
            $(this).html("<i class='fa fa-fw fa-"+value+"'></i>");
        },
        source: [
            {value: "public"    , text: "public"    },
            {value: "hidden"    , text: "hidden"    },
            {value: "private"   , text: "private"   },
            {value: "registered", text: "registered"},
            {value: "none"      , text: "none"      },
            {value: "deleted"   , text: "deleted"   }
        ]
    });

    $("a.add").unbind("click").on("click",function(e) {
        e.preventDefault();
        $(".modal-title").text(cattranslate("Add page"));
        $("#modal_dialog form").fieldset_to_tabs();
        $("div.fbform form#be_page_settings ul.nav.nav-tabs.inner a:last").tab("show");
        $("#modal_dialog").modal("show");
        var _this = $(this);
        $(".modal-content button.btn-primary").unbind("click").on("click",function(e) {
            //console.log($("div.fbform form#be_page_settings").serialize());
            e.preventDefault();
            $("#modal_dialog").modal("hide");
            $.ajax({
                type    : "POST",
                url     : CAT_ADMIN_URL+"/page/add/",
                dataType: "json",
                data    : $("div.fbform form#be_page_add").serialize(),
                success : function(data, status) {
                    BCGrowl(data.message,data.success);
                    window.location.href = CAT_ADMIN_URL + "/page/edit/" + data.page_id
                }
            });
        });
    });
});

