        <table class="table">
            <thead>
                <tr>
                    <th>{* icons column *}</th>
                    <th>{translate('Role ID')}</th>
                    <th>{translate('Title')}</th>
                    <th>{translate('Description')}</th>
                    <th>{translate('Groups')}</th>
                    <th>{translate('Users')}</th>
                    <th>{translate('Permissions')}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $roles role}
                <tr>
                    <td>
                        {if $role.builtin == 'Y'}<span class="fa fa-fw fa-anchor"></span>{/if}
                        {if $PERMS.roles_perms}<a href="#" class="perms" data-toggle="tooltip" title="{translate('Manage role permissions')}" data-role-id="{$role.role_id}"><span class="fa fa-fw fa-key"></span></a>{/if}
                    </td>
                    <td>{$role.role_id}</td>
                    <td><a href="#" class="editable" data-name="title" data-type="text" data-pk="{$role.role_id}" data-url="{$CAT_ADMIN_URL}/roles/edit" data-title="{translate('Title')}">{$role.title}</a></td>
                    <td><a href="#" class="editable" data-name="description" data-type="text" data-pk="{$role.role_id}" data-url="{$CAT_ADMIN_URL}/roles/edit" data-title="{translate('Description')}">{translate($role.description)}</a></td>
                    <td>0</td>
                    <td>{$role.user_count}</td>
                    <td>{$role.perm_count}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {include 'legend_roles.tpl'} {include 'legend.tpl'}
        {include(file='fuelux_modal.tpl' modal_id='modal_perms' modal_title='', modal_text='')}
        
{if $PERMS.roles_add}
        <form role="form" method="post" class="form-inline ajax" action="{$CAT_ADMIN_URL}/roles/create">
            {translate('Create new')}
            <div class="form-group">
                <input type="text" placeholder="{translate('Title')}" class="form-control" name="role_name" id="role_name" />
            </div>
            <div class="form-group">
                <input type="text" placeholder="{translate('Description')}" class="form-control" name="role_description" id="role_description" />
            </div>
            <input type="submit" class="btn btn-primary" />
            <input type="reset" class="btn btn-link" type="button" />
        </form>
{/if}

{if $PERMS.roles_perms}
        <table id="perm_tree_table" style="display:none">
            <colgroup>
                <col width="*"></col>
                <col width="*"></col>
            </colgroup>
            <thead>
                <tr><th>{translate('Permission')}</th><th>{translate('Description')}</th></tr>
            </thead>
            <tbody>
            </tbody>
        </table>
{/if}


        <script type="text/javascript">
        //<![CDATA[

        var bs_perm_tree_convert_data = function(val)
        {
            var converted = val;
            converted.description = val.description;
            if(val.children) {
                converted.children = val.children;
            }
            return converted;
        };
        var bs_perm_tree_expandAll = function()
        {
            var perm_tree = $("#perm_tree_table").fancytree("getTree");
            perm_tree.visit(function(node) {
                node.setExpanded(true);
            });
        }

        // move fancytree into modal
        $("#perm_tree_table").appendTo('div.modal-body').show();


        $("#perm_tree_table").fancytree({
            source: {
                url: "{$CAT_ADMIN_URL}/permissions/list/recursive",
            },
            extensions: ["table"],
			checkbox: true,
            icon: false,
			selectMode: 2,
            table: {
				indentation: 20,      // indent 20px per node level
				nodeColumnIdx: 0     // render the node title into the 2nd column
			},
            postProcess: function(event, data) { // convert data for fancytree
                var converted = new Array;
                jQuery.each(data.response, function(i, val) {
                    converted.push(bs_perm_tree_convert_data(val));
                });
                data.result = converted;
            },
			init: function(event, data, flag) {
                bs_perm_tree_expandAll();
			},
            select: function(event, data) {
                var node = data.node;
                if(data.node.isSelected())
                {
                    //console.log(node.getParentList());
                    parents = node.getParentList(false, true);
            		for(i=0, l=parents.length; i<l; i++) {
            			parents[i].setSelected();
            		}
                }
                else
                {
                    children = node.getChildren();
                    for(i=0, l=children.length; i<l; i++) {
            			children[i].setSelected(false);
            		}
                }
            },
            renderColumns: function(event, data) {
                // enable for debugging
                //console.log('data',data);
    			var    node = data.node,
    				$tdList = $(node.tr).find(">td");
                $tdList.eq(1).html(node.data.description);
            }
        });

        $('a.perms').on('click',function(e) {
            e.preventDefault();
            $.ajax({
                type    : 'POST',
                url     : "{$CAT_ADMIN_URL}/permissions/byrole/" + $(this).data('role-id'),
                dataType: 'json',
                success : function(data, status) {
                    // reset
                    $("#perm_tree_table").fancytree("getTree").visit(function(node) { node.setSelected(false); });
                    jQuery.each(data, function(i, item) {
                        var node = $("#perm_tree_table").fancytree("getTree").findFirst(item.title);
                        if(node) {
                            node.setSelected();
                        }
                    });
                    // requires selectmode 3!
                    //$("#perm_tree_table").fancytree("getRootNode").fixSelection3FromEndNodes();
                }
            });
            $('div#modal_perms').modal('show');
            $('button.btn-primary').on('click',function(e) {
                $('#modal_perms').modal('hide');
                var selectedNodes = $("#perm_tree_table").fancytree("getTree").getSelectedNodes();
                // enable for debugging
                //console.log(selectedNodes);
                if(selectedNodes.length) {
                    var data = new Array;
                    for(i=0;i<selectedNodes.length;i++) {
                        data.push(selectedNodes[i].data.perm_id);
                    }
                    // enable for debugging
                    //console.log(data);
                    $.ajax({
                        type    : 'POST',
                        url     : "{$CAT_ADMIN_URL}/roles/saveperms/2",
                        dataType: 'json',
                        data    : { 'perms': data },
                        success : function(data, status) {
                            $('div.infopanel span#message').html(cattranslate('Permissions successfully saved'));
                            $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                        }
                    });
                }
            });
        });

        
        //]]>
        </script>