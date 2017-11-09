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
                        <span class="fa fa-fw{if $group.group_builtin == 'Y'} fa-anchor" title="{translate('Built-in objects cannot be removed')}"{else}"{/if}></span>
                        {if user_has_perm('groups_delete') && $group.group_builtin != 'Y'}
                        <a href="#" class="delete" title="{translate('Delete group')}" data-url="{$CAT_ADMIN_URL}/groups/delete" data-id="{$group.group_id}" data-name="{$group.group_title}"><span class="fa fa-fw fa-trash text-danger"></span></a>{else}<span class="fa fa-fw"></span>
                        {/if}
                        {if user_has_perm('groups_users')}
                        {if $group.member_count > 0}<a href="#" class="members" data-toggle="tooltip" title="{translate('Manage group members')}" data-group-id="{$group.group_id}"><span class="fa fa-fw fa-groups"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                        <a href="#" class="add_member" data-toggle="tooltip" title="{translate('Add group members')}" data-group-id="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/users""><span class="fa fa-fw fa-user-plus text-success"></span></a>
                        {else}
                        <span class="fa fa-fw"></span>
                        {/if}
                    </td>
                    <td>{$group.group_id}</td>
                    <td><a href="#" class="editable" data-name="group_title" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" title="{translate('Title')}">{$group.group_title}</a></td>
                    <td><a href="#" class="editable" data-name="group_description" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" title="{translate('Description')}">{$group.group_description}</a></td>
                    <td>{$group.member_count}</td>
                    <td>{$group.role_count}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        <span class="fa fa-fw fa-groups text-primary"></span> = {translate('Edit group members')}
        <span class="fa fa-fw fa-user-plus text-success"></span> = {translate('Add group members')}
        <span title="{translate('Built-in objects cannot be removed')}"><span class="fa fa-fw fa-anchor"></span> = {translate('Built in')}</span>
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
                            <a href="#" class="deleteuser" title="{translate('Remove group member')}" data-id="" data-username="" data-url="{$CAT_ADMIN_URL}/groups/deleteuser">
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
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>