<table class="table dtable">
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
                <span class="fa fa-fw{if $user.protected == 'Y'} fa-anchor{/if}"></span>
                {if user_has_perm('users_edit')}<a href="#" class="tfa" data-url="{$CAT_ADMIN_URL}/users/tfa" data-id="{$user.user_id}" title="{translate('Two factor authentication is')}: {if $user.tfa_enabled == 'N'}dis{else}en{/if}abled"><span class="text-success fa fa-fw fa-{if $user.tfa_enabled == 'N'}un{/if}lock {if $user.tfa_enabled == 'N'}yellow{/if}"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                {if $user.protected != 'Y'}
                {if user_has_perm('users_delete')}<a href="#" class="delete" data-url="{$CAT_ADMIN_URL}/users/delete" data-id="{$user.user_id}" data-name="{$user.username}"><span class="fa fa-fw fa-trash text-danger"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                {/if}
            </td>
            <td>{$user.user_id}</td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="title" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} data-title="{translate('Username')}">{$user.username}</a></td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="display_name" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} data-title="{translate('Display name')}">{$user.display_name}</a></td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="email" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} data-title="{translate('eMail')}">{$user.email}</a></td>
            <td>
                {if user_has_perm('users_edit') && count($user.groups)}
                <div class="pillbox" data-initialize="pillbox" id="groupList">
                    <ul class="clearfix pill-group">
                        {foreach $user.groups group}
                        <li class="btn btn-default btn-xs pill" data-value="foo">
                            <span{if $group.primary == 'Y'} class="text-primary"{/if}>{$group.group_title}</span>
                            <span class="glyphicon glyphicon-close"><span class="sr-only">{translate('Remove')}</span></span>
                        </li>
                        {/foreach}
                    </ul>
                </div>
                {else}
                    {foreach $user.groups group implode=", "}{$group.group_title}{/foreach}
                {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>

<span class="text-success fa fa-fw fa-lock"></span> = {translate('Two-Step Authentication enabled')}
<span class="text-success fa fa-fw fa-unlock"></span> = {translate('Two-Step Authentication disabled')}
<span class="fa fa-fw fa-anchor"></span> = {translate('Built in')}
<span class="fa fa-fw fa-trash text-danger"></span> = {translate('Delete')}<br /><br />

{$file = cat('modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        var dtable = $('table.dtable').DataTable({
            mark: true,
            stateSave: true,
            orderClasses: false{if cat_file_exists($file)},
            language: {
                url: "{cat_asset_url($file,'js')}"
            }
            {/if}
        });
    });
//]]>
</script>