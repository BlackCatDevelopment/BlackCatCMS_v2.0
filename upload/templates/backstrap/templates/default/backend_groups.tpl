        <table class="table dtable">
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
                        <span class="fa fa-fw{if $group.group_builtin == 'Y'} fa-anchor" data-title="{translate('Built-in objects cannot be removed')}"{else}"{/if}></span>
                        {if user_has_perm('groups_delete') && $group.group_builtin != 'Y'}
                        <a href="#" class="delete" data-title="{translate('Delete group')}" data-url="{$CAT_ADMIN_URL}/groups/delete" data-id="{$group.group_id}" data-name="{$group.group_title}"><span class="fa fa-fw fa-trash text-danger"></span></a>{else}<span class="fa fa-fw"></span>
                        {/if}
                        {if user_has_perm('groups_users')}
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
        <span data-title="{translate('Built-in objects cannot be removed')}"><span class="fa fa-fw fa-anchor"></span> = {translate('Built in')}</span>
        <span class="fa fa-fw fa-trash text-danger"></span> = {translate('Delete')}<br /><br />

        {if user_has_perm('groups_add')}
        <form role="form" method="post" class="form-inline ajax" action="{$CAT_ADMIN_URL}/groups/create">
            {translate('Create new group')}
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

        {include(file='backend_modal.tpl' modal_id='modal_group' modal_savebtn='1')}

        <div id="bs_userlist" style="display:none">
            <table class="table">
                <thead>
                    <tr>
                        <th>{* icons *}</th>
                        <th>{translate('User ID')}</th>
                        <th>{translate('Login name')}</th>
                        <th>{translate('Display name')}</th>
                        <th>{translate('eMail')}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-col="1">
                            {if user_has_perm('groups_users')}
                            <a href="#" class="deleteuser" data-title="{translate('Remove group member')}" data-id="" data-username="" data-url="{$CAT_ADMIN_URL}/groups/deleteuser">
                              <span class="fa fa-fw fa-trash text-danger"></span>
                            </a>
                            {/if}
                        </td>
                        <td data-col="2"></td>
                        <td data-col="3"></td>
                        <td data-col="4"></td>
                        <td data-col="5"></td>
                    </tr>
                </tbody>
            </table>
        </div>


        {* get the name of the language file; allows to check if it exists *}
        {$file = cat('modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
        <script type="text/javascript">
        //<![CDATA[
            $(function() {

                // reset infopanel
                function infoPanelReset() {
                    $('div.infopanel span#message').html('');
                    $('div.infopanel').hide();
                }

                var dtable = $('table.dtable').DataTable({
                    mark: true,
                    stateSave: true,
                    orderClasses: false{if cat_file_exists($file)},
                    language: {
                        url: "{cat_asset_url($file,'js')}"
                    }
                    {/if}
                });
                // delete a group member
                $(document).on('click', 'a.deleteuser', function(event) {
                    event.preventDefault();
                    infoPanelReset();
                    var id = $(this).data('id');
                    $('#bsmodal_groupLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> {translate("Remove group member")}: '+$(this).data('username'));
                    $('#modal_group').modal('show');
                    $('div.modal-body').html('{translate("Do you really want to remove this group member?")}');
                    $('#modal_users').modal('show');
                    var _this = $(this);
                    $('button.btn-primary').on('click',function(e) {
                        $('#modal_users').modal('hide');
                        $.ajax({
                            type    : 'POST',
                            url     : _this.data('url')+"/"+id,
                            dataType: 'json',
                            success : function(data, status) {
                                _this.parent().parent().remove();
                                $('div.infopanel span#message').html("{translate('Group member successfully removed')}");
                                $('div.infopanel').removeClass('alert-danger').addClass('alert-success').show();
                            }
                        });
                    });
                });
                // delete a group
                $('a.delete').on('click', function(event) {
                    event.preventDefault();
                    // reset infopanel
                    infoPanelReset();
                    var id = $(this).data('group-id');
                    $('#bsmodal_groupLabel').html('<span id="modalicon" class="fa fa-fw fa-warning"></span> {translate("Delete group")}: '+$(this).data('name'));
                    $('div.modal-body').html("{translate('Do you really want to delete this group?')}");
                    $('#modal_group').modal('show');
                });
                // add a group member
                $('a.add_member').on('click', function(event) {
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
                // show group members
                $('a.members').on('click', function(event) {
                    var id = $(this).data('group-id');
                    if($('.bs_group_users').length) {
                        // remove other
                        $('tr').removeClass('highlight');
                        $('.bs_group_users').hide('slow').remove();
                    }
                    var _this = $(this);
                    $.ajax({
                        type    : 'POST',
                        url     : '{$CAT_ADMIN_URL}/users/bygroup/' + id,
                        dataType: 'json',
                        success : function(data, status) {
                            // activate for debugging:
                            //console.log(data);
                            if(data.length) {
                                var table = $('div#bs_userlist table').clone();
                                var tbody = $(table).find('tbody');
                                var line  = $(tbody).find('tr').remove();
                                for(i=0;i<data.length;i++)
                                {
                                    // activate for debugging:
                                    //console.log('adding data line',data[i]);
                                    var curr_line = $(line).clone();
                                    $(curr_line).find('td[data-col="2"]').html(data[i].id);
                                    $(curr_line).find('td[data-col="3"]').html(data[i].username);
                                    $(curr_line).find('td[data-col="4"]').html(data[i].display_name);
                                    $(curr_line).find('td[data-col="5"]').html(data[i].email);
                                    if(data[i].protected == 'Y') {
                                        $(curr_line).find('td[data-col="1"]').html('<span class="fa fa-fw fa-anchor" data-title="{translate("Built-in objects must not be removed")}"></span>');
                                    }
                                    else {
                                        $(curr_line).find('td[data-col="1"] > a').attr('data-id',data[i].id);
                                        $(curr_line).find('td[data-col="1"] > a').attr('data-username',data[i].username);
                                    }
                                    $(curr_line).appendTo($(tbody));
                                }
                                var parent = $(_this).parent().parent(),
                                    td     = $('<td>').prop('colspan',6).html(table.show());
                                    row    = $('<tr>').addClass('bs_group_users highlight').html(td);
                                row.insertAfter(parent).show('slow');
                                parent.addClass('highlight');
                                $('[data-title!=""]').qtip({
                                    content: { attr: 'data-title' },
                                    style: { classes: 'qtip-bootstrap' }
                                });
                            }
                        }
                    });
                    event.preventDefault();
                });
            });
        //]]>
        </script>