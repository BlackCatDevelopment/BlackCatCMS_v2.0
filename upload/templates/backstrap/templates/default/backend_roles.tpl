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
                    <td>{if $role.builtin == 'Y'}<span class="fa fa-fw fa-anchor"></span>{/if}</td>
                    <td>{$role.role_id}</td>
                    <td><a href="#" class="editable" data-name="title" data-type="text" data-pk="{$role.role_id}" data-url="{$CAT_ADMIN_URL}/roles/edit" data-title="{translate('Title')}">{$role.title}</a></td>
                    <td><a href="#" class="editable" data-name="description" data-type="text" data-pk="{$role.role_id}" data-url="{$CAT_ADMIN_URL}/roles/edit" data-title="{translate('Description')}">{$role.description}</a></td>
                    <td>0</td>
                    <td>{$role.user_count}</td>
                    <td>{$role.perm_count}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {include 'legend.tpl'}

        {if $perms.roles_add}
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

        <script type="text/javascript">
        //<![CDATA[
            
        //]]>
        </script>