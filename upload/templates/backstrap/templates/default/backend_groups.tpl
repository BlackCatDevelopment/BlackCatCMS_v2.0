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
                        {if $PERMS.groups_delete && $group.group_builtin != 'Y'}
                        <a href="#" class="delete" data-toggle="tooltip" title="{translate('Delete group')}" data-url="{$CAT_ADMIN_URL}/groups/delete" data-id="{$group.group_id}" data-name="{$group.group_title}"><span class="fa fa-fw fa-trash red"></span></a>{else}<span class="fa fa-fw"></span>
                        {/if}
                        {if $PERMS.groups_users}
                        {if $group.member_count > 0}<a href="#" class="members" data-toggle="tooltip" title="{translate('Manage group members')}" data-group-id="{$group.group_id}"><span class="fa fa-fw fa-groups"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                        <a href="#" class="add_member" data-toggle="tooltip" title="{translate('Add group members')}" data-group-id="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/users""><span class="fa fa-fw fa-user-plus text-success"></span></a>
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

        <span class="fa fa-fw fa-groups text-primary"></span> = {translate('Edit group members')}
        <span class="fa fa-fw fa-user-plus text-success"></span> = {translate('Add group members')}
        {include 'legend.tpl'}

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
        {include(file='fuelux_modal.tpl' modal_id='modal_users' modal_title='Add group members', modal_text='Choose the users you wish to add and click [Save]')}

        <script type="text/javascript">
        //<![CDATA[
            function infoPanelReset() {
                $('div.infopanel span#message').html('');
                $('div.infopanel').hide();
                $('div.infopanel').removeClass('alert-success').addClass('alert-danger');
            }
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
                    {if $PERMS.users_delete}
                        if(typeof '{$PERMS.users_delete}' != 'undefined' && rowData.user_id != 1)
                        {
                            customMarkup = '<a class="deleteuser" href="#" data-url="{$CAT_ADMIN_URL}/groups/'
                                         + rowData.group_id + '/deleteuser/' + rowData.user_id
                                         + '" data-username="'+rowData.username+'"><span class="fa fa-fw fa-trash red"></span></a>';
                        }
                    {/if}
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
                $(document).on('click', 'a.deleteuser', function(event) {
                    event.preventDefault();
                    // reset infopanel
                    infoPanelReset();
                    $('#myModalLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> '+cattranslate('Remove group member'));
                    $('div.modal-body').html(cattranslate('Do you really want to remove this group member?')+'<br />'+cattranslate('Title')+': '+$(this).data('username'));
                    $('#modal_users').modal('show');
                    var _this = $(this);
                    $('button.btn-primary').on('click',function(e) {
                        $('#modal_users').modal('hide');
                        $.ajax({
                            type    : 'POST',
                            url     : _this.data('url'),
                            dataType: 'json',
                            success : function(data, status) {
                                _this.parent().parent().remove();
                                $('div.infopanel span#message').html(cattranslate('Group member successfully removed'));
                                $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                            }
                        });
                    });
                });
                $('a.delete').on('click', function(event) {
                    event.preventDefault();
                    // reset infopanel
                    infoPanelReset();
                    var id = $(this).data('group-id');
                    $('#myModalLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> '+cattranslate('Delete group'));
                    $('div.modal-body').html(cattranslate('Do you really want to delete this group?')+'<br />'+cattranslate('Title')+': '+$(this).data('name'));
                    $('#modal_users').modal('show');
                });
                $('a.add_member').on('click', function(event) {
                    // reset infopanel
                    infoPanelReset();
                    var id = $(this).data('group-id');
                    $.ajax({
                        type    : 'POST',
                        url     : '{$CAT_ADMIN_URL}/users/notingroup/' + id,
                        dataType: 'json',
                        success : function(data, status) {
                            // activate for debugging:
                            //console.log(data);
                            // add select box to modal
                            if(data.length)
                            {
                                if($('div.modal-body').find('select').length) {
                                    $('div.modal-body select').remove();
                                }
                                $('div.modal-body').append('<form><select id="users" multiple="multiple">');
                                var select = $('select#users');
                                for(i=0;i<data.length;i++)
                                {
                                    select.append('<option value="'+data[i].user_id+'">'+data[i].username+'</option>');
                                }
                                $('#modal_users').modal('show');
                                var _this = $(this);
                                $('button.btn-primary').on('click',function(e) {
                                    $('#modal_users').modal('hide');
                                    if($('select#users :selected').length)
                                    {
                                        $.ajax({
                                            type    : 'POST',
                                            url     : '{$CAT_ADMIN_URL}/groups/addmember/',
                                            dataType: 'json',
                                            data    : $(_this).find('form').serialize(),
                                            success : function(data, status) {
                                                _this.parent().parent().remove();
                                                $('div.infopanel span#message').html(cattranslate('Group member successfully removed'));
                                                $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                                            }
                                        });
                                    }
                                });
                            }
                            else {
                                $('div.infopanel span#message').html(
                                    cattranslate('No addable users found') + '<br />' +
                                    cattranslate('Please note') + ': ' +
                                    cattranslate('Users of group "Administrators" and users that are already member of this group cannot be added.')
                                );
                                $('div.infopanel').removeClass('alert-danger').addClass('alert-warning').show();
                            }
                        }
                    });
                });

                $('a.members').on('click', function(event) {
                    // reset infopanel
                    $('div.infopanel span#message').html('');
                    $('div.infopanel').hide();

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
                                console.log(data);
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