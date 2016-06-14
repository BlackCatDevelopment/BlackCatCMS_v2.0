        <table class="table">
            <thead>
                <tr>
                    <th>{* icons column *}</th>
                    <th>{translate('User ID')}</th>
                    <th>{translate('Login name')}</th>
                    <th>{translate('Display name')}</th>
                    <th>{translate('eMail')}</th>
                    <th>{translate('Groups')}</th>
                </tr>
            </thead>
            <tbody>
            {foreach $users user}
                <tr>
                    <td>
                        <span class="fa fa-fw{if $user.user_id == 1} fa-anchor{/if}"></span>
                        {if $PERMS.users_delete && $user.user_id != 1}<a href="#" class="ajax" data-url="{$CAT_ADMIN_URL}/users/delete" data-id="{$user.user_id}"><span class="fa fa-fw fa-trash red"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                    </td>
                    <td>{$user.user_id}</td>
                    <td><a href="#" class="editable" data-name="title" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit" data-title="{translate('Username')}">{$user.username}</a></td>
                    <td><a href="#" class="editable" data-name="display_name" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit" data-title="{translate('Display name')}">{$user.display_name}</a></td>
                    <td><a href="#" class="editable" data-name="email" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit" data-title="{translate('eMail')}">{$user.email}</a></td>
                    <td>
                        <div class="pillbox" data-initialize="pillbox" id="groupList">
                            <ul class="clearfix pill-group">
                                {foreach $user.groups group}
                                <li class="btn btn-default pill" data-value="foo">
                                    <span{if $group.primary == 'Y'} class="text-primary"{/if}>{$group.group_title}</span>
                                    <span class="glyphicon glyphicon-close"><span class="sr-only">{translate('Remove')}</span></span>
                                </li>
                                {/foreach}
                            </ul>
                        </div>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>

        {include 'legend.tpl'}

        {if $PERMS.users_add}
        <form role="form" method="post" class="form-inline ajax" action="{$CAT_ADMIN_URL}/users/create">
            {translate('Create new')}
            <div class="form-group">
                <input type="text" placeholder="{translate('Title')}" class="form-control" name="user_name" id="user_name" />
            </div>
            <div class="form-group">
                <input type="text" placeholder="{translate('Description')}" class="form-control" name="user_description" id="user_description" />
            </div>
            <input type="submit" class="btn btn-primary" />
            <input type="reset" class="btn btn-link" type="button" />
        </form>
        {/if}
