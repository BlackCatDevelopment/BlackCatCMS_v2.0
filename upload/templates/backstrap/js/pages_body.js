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

    $(".space").sortable({
        connectWith:".space",
        tolerance:"intersect",
        over:function(event,ui){ },
        receive:function(event, ui){
            //var position = $(ui.item).offset();
            //var newWidth = fullwidth - position.left + container_offset.left + 5;
            //$(ui.item).find("div.card").css("width",newWidth);
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

});

