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
                        {if user_has_perm('roles_perms')}<a href="#" class="perms" data-toggle="tooltip" title="{translate('Manage role permissions')}" data-role-id="{$role.role_id}" data-role-name="{$role.title}"><span class="fa fa-fw fa-key"></span></a>{/if}
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

{if user_has_perm('roles_add')}
        <form role="form" method="post" class="form-inline" action="{$CAT_ADMIN_URL}/roles/create">
            {translate('New role')}:&nbsp;
            <input type="text" placeholder="{translate('Title')}" class="form-control mb-2 mr-sm-2" name="role_name" id="role_name" />
            <input type="text" placeholder="{translate('Description')}" class="form-control mb-2 mr-sm-2" name="role_description" id="role_description" />
            <input type="submit" class="btn btn-primary" value="{translate('Save')}" />
            <input type="reset" class="btn btn-link" type="button" />
        </form>
{/if}

{if user_has_perm('roles_perms')}
        <div id="perm_tree_table" style="display:none">
            {translate('To select a permission with all children, double click on the checkbox.')}<br />
            [ <a href="#" class="bsExpandAll">{translate('Expand all')}</a> | <a href="#" class="bsCloseAll">{translate('Close all')}</a> ]<br />
            {$perms}
        </div>
{/if}