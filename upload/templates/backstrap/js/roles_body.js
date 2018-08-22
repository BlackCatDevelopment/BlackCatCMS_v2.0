$(function() {
    $('a.perms').on('click',function(e) {
        e.preventDefault();
        var roleID = $(this).data('role-id');
        // add fancytree to modal, creating a fresh ID
        var treeID = 'permTree'+$(this).data('role-id');
        var tree   = $("#perm_tree_table").clone();
        tree.attr('id',treeID);

        $('div#bsDialog .modal-title').text($.cattranslate('Set permissions for role') + ': "' + $(this).data('role-name') + '"');
        $('div#bsDialog div.modal-body').html(tree.show());

        var DELAY = 700, clicks = 0, timer = null;

        $("#"+treeID).fancytree({
            autoScroll: true, // Automatically scroll nodes into visible area
            checkbox: true, // Show checkboxes
            debugLevel: 0, // 0:quiet, 1:errors, 2:warnings, 3:infos, 4:debug
            icon: false, // Display node icons
            click: function(event) {
                // catch doubleclicks
                clicks++;  //count clicks
                if(clicks === 1) {
                    timer = setTimeout(function() {
                        clicks = 0;             //after action performed, reset counter
                    }, DELAY);
                } else {
                    clearTimeout(timer);    //prevent single-click action
                    clicks = 0;             //after action performed, reset counter
                    return false;
                }
            },
            // if a node having children is selected, select all children
            dblclick: function(event, data) {
                var node = data.node;
                if(data.targetType == 'checkbox' && node.hasChildren())
                {
                    node.visit(function(n) {
                        n.setSelected(node.isSelected());
                    });
                }
            }
        });

        // get data
        $.ajax({
            type    : 'POST',
            url     : CAT_ADMIN_URL+"/permissions/byrole/" + roleID,
            dataType: 'json',
            success : function(data, status) {
                var ftree = $("#"+treeID).fancytree("getTree");
                jQuery.each(data, function(i, item) {
                    node = ftree.getNodeByKey(item.perm_id);
                    node.setSelected();
                });
                $('a.bsExpandAll').on('click',function(e) {
                    ftree.visit(function(node) {
                        node.setExpanded();
                    });
                });
                $('a.bsCloseAll').on('click',function(e) {
                    ftree.visit(function(node) {
                        node.setExpanded(false);
                    });
                });
                // auto-open first 3 levels
                ftree.visit(function(node) {
                    if(node.getLevel()<=3) {
                        node.setExpanded();
                    }
                });
                $('div#bsDialog').modal('show');
                $('button.btn-primary').on('click',function(e) {
                    var selectedNodes = ftree.getSelectedNodes();
                    // enable for debugging
                    // console.log('seletectedNodes: ',selectedNodes);
                    $('div#bsDialog').modal('hide');
                    $('div#bsDialog .modal-body').html('');
                    if(selectedNodes.length) {
                        var data = new Array;
                        for(i=0;i<selectedNodes.length;i++) {
                            data.push(selectedNodes[i].key);
                        }
                        // enable for debugging
                        // console.log('data to send: ', data);
                        $.ajax({
                            type    : 'POST',
                            url     : CAT_ADMIN_URL+"/roles/saveperms/" + roleID,
                            dataType: 'json',
                            data    : { 'perms': data },
                            success : function(data, status) {
                                $('div.infopanel span#message').html($.cattranslate('Permissions successfully saved'));
                                $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                            }
                        });
                    }
                });
            }
        });

    });
});