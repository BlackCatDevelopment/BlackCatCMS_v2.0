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
        $(obj).find("div.card").each(function() {
            var position = $(this).offset();
            var newWidth = fullwidth - position.left + container_offset.left;
            $(this).css("width",newWidth);
        });
    }

    // $("#element").children().uniqueId().end().
    $(".space").sortable({
    //$(".space").children().uniqueId().end().sortable({
        connectWith:".space",
        tolerance:"intersect",
        over:function(event,ui){ },
        receive:function(event, ui){
            calcWidth($(".treecontainer > ul"));
        },
        update:function(event,ui){
            $.ajax({
                type    : 'POST',
                url     : CAT_ADMIN_URL + '/page/reorder',
                data    : {
                    page_id:  ui.item.data('id'),
                    parent:   ui.item.parent().data('id'),
                    position: ui.item.index()+1
                },
                dataType: 'json',
                success : function(data, status) {
                    if(data.success==true) {
                        BCGrowl($.cattranslate('Success'),true);
                        window.location.href = CAT_ADMIN_URL + '/pages'
                    } else {
                        BCGrowl(data.message);
                    }
                }
            });
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

});

