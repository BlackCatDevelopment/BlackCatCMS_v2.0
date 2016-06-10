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
                        <span class="fa fa-fw{if $group.builtin == 'Y'} fa-anchor{/if}"></span>
                        {if $perms.groups_delete && $group.builtin != 'Y'}<a href="#" class="ajax" data-url="{$CAT_ADMIN_URL}/groups/delete" data-id="{$group.group_id}"><span class="fa fa-fw fa-trash red"></span></a>{/if}
                    </td>
                    <td>{$group.group_id}</td>
                    <td><a href="#" class="editable" data-name="title" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" data-title="{translate('Title')}">{$group.title}</a></td>
                    <td><a href="#" class="editable" data-name="description" data-type="text" data-pk="{$group.group_id}" data-url="{$CAT_ADMIN_URL}/groups/edit" data-title="{translate('Description')}">{$group.description}</a></td>
                    <td>{$group.member_count}</td>
                    <td>{$group.role_count}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {include 'legend.tpl'}

        {if $perms.groups_add}
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
