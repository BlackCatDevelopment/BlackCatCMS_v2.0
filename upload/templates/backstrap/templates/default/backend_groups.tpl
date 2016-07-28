        <table class="table">
            <thead>
                <tr>
                    <th>{* icons column *}</th>
                    <th>{translate('Group ID')}</th>
                    <th>{translate('Title')}</th>
                    <th>{translate('Description')}</th>
                    <th>{translate('Users')}</th>
                    <th>{translate('Roles')}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $groups group}
                <tr>
                    <td>
                        <span class="fa fa-fw{if $group.group_builtin == 'Y'} fa-anchor{/if}"></span>
                        {if $PERMS.groups_delete && $group.group_builtin != 'Y'}<a href="#" class="ajax" data-url="{$CAT_ADMIN_URL}/groups/delete" data-id="{$group.group_id}"><span class="fa fa-fw fa-trash red"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                        {if $PERMS.groups_users}
                        <a href="" class="members" title="{translate('Manage group members')}" data-group-id="{$group.group_id}"><span class="fa fa-fw fa-groups"></span></a>
                        <a href="#" data-url="{$CAT_ADMIN_URL}/users" class="add_member" title="{translate('Add group members')}" data-group-id="{$group.group_id}"><span class="fa fa-fw fa-user-plus text-success"></span></a>
                        {else}
                        <span class="fa fa-fw"></span>
                        {/if}
                    </td>
                    <td>{$group.group_id}</td>
                    <td><a href="#" class="editable" data-name="group_title" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" data-title="{translate('Title')}">{$group.group_title}</a></td>
                    <td><a href="#" class="editable" data-name="group_description" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" data-title="{translate('Description')}">{$group.group_description}</a></td>
                    <td>{$group.member_count}</td>
                    <td>{$group.role_count}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        <span class="fa fa-fw fa-groups text-primary"></span> = {translate('Edit group members')} {include 'legend.tpl'}

        {if $PERMS.groups_add}
        <form role="form" method="post" class="form-inline ajax" action="{$CAT_ADMIN_URL}/groups/create">
            {translate('Create new')}
            <div class="form-group">
                <input type="text" placeholder="{translate('Title')}" class="form-control" name="group_name" id="group_name" />
            </div>
            <div class="form-group">
                <input type="text" placeholder="{translate('Description')}" class="form-control" name="group_description" id="group_description" />
            </div>
            <input type="submit" class="btn btn-primary" />
            <input type="reset" class="btn btn-link" type="button" />
        </form>
        {/if}

        {include(file='fuelux_repeater.tpl' repeater_id='bs_rep_groups' repeater_title='Users')}

        <script type="text/javascript">
        //<![CDATA[
            function customColumnRenderer(helpers, callback) {
                // Determine what column is being rendered and review
                var column = helpers.columnAttr;
                // get all the data for the entire row
                var rowData = helpers.rowData;
                var customMarkup = '';
                // Only override the output for specific columns.
                // This will default to output the text value of the row item
                switch(column) {
                    case 'tpl_icon_delete':
                        if(typeof '{$PERMS.users_delete}' != 'undefined' && rowData.user_id != 1)
                        {
                            customMarkup = '<a class="ajax" href="{$CAT_ADMIN_URL}/users/delete/'
                                         + rowData.user_id
                                         + '"><span class="fa fa-fw fa-trash red"></span></a>';
                        }
                        break;
                    default:
                        // otherwise, just use the existing text value
                        customMarkup = helpers.item.text();
                        break;
                }
                helpers.item.html(customMarkup);
                callback();
            }
            $(function() {
                $('a.add_member').on('click', function(event) {
                    var id = $(this).data('group-id');
                    $.ajax({
                        type    : 'POST',
                        url     : '{$CAT_ADMIN_URL}/users/bygroup/' + id,
                        dataType: 'json',
                        success : function(data, status) {
                            // activate for debugging:
                            //console.log(data);
                        }
                    });
                });

                $('a.members').on('click', function(event) {
                    var id = $(this).data('group-id');
                    if($('#bs_rep_groups_'+id).length) {
                        // remove
                        $('tr').removeClass('highlight');
                        $('#bs_rep_groups_'+id).hide('slow').parent().parent().remove();
                    }
                    else
                    {
                        var _this = $(this);
                        $.ajax({
                            type    : 'POST',
                            url     : '{$CAT_ADMIN_URL}/users/bygroup/' + id,
                            dataType: 'json',
                            success : function(data, status) {
                                // activate for debugging:
                                //console.log(data);
                                var rep = $('div#bs_rep_groups').clone().prop('id','bs_rep_groups_'+id),
                                    pg  = 1;
                                if(data.length && data.length > 10) {
                                    pg = data.length / 10;
                                }
                                rep.repeater({
                                    list_columnRendered: customColumnRenderer,
                                    dataSource: function (options, callback) {
                                        // define the datasource
                                        var dataSource = {
                                            'page': 0,
                                            'pages': pg,
                                            'count': data.length,
                                            'start': 0,
                                            'end': data.length,
                                            'columns': [{
                                                label: '',
                                                property: 'tpl_icon_delete'
                                            },{
                            					label: cattranslate('User ID'),
                            					property: 'user_id',
                            					sortable: true
                            				},{
                            					label: cattranslate('Login name'),
                            					property: 'username',
                            					sortable: true
                                            },{
                            					label: cattranslate('Display name'),
                            					property: 'display_name',
                            					sortable: true
    				                        },{
                                                label: cattranslate('eMail'),
                                                property: 'email',
                                                sortable: true
                                            }],
                                            'items': data
                                        };
                                        // pass the datasource back to the repeater
                                        callback(dataSource);
                                    }
                                });
                                var parent = $(_this).parent().parent(),
                                    td     = $('<td>').prop('colspan',6).html(rep.show());
                                var line   = $('<tr>').addClass('highlight').html(td).hide();
                                line.insertAfter(parent).show('slow');
                                parent.addClass('highlight');
                                rep.repeater('resize');
                            }
                        });
                    };
                    event.preventDefault();
                });
            });
        //]]>
        </script>