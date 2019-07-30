<table class="table dtable">
    <thead>
        <tr>
            <th>{* icons column *}</th>
            <th>{translate('User ID')}</th>
            <th>{translate('Login name')}</th>
            <th>{translate('Display name')}</th>
            <th>{translate('eMail')}</th>
            <th>{translate('Groups')}</th>
            <th>{translate('WYSIWYG-Editor')}</th>
        </tr>
    </thead>
    <tbody>
    {foreach $users user}
        <tr>
            <td>
                <span class="fa fa-fw{if $user.protected == 'Y'} fa-anchor" title="{translate('This is a built-in-user, so you cannot change it')}{/if}"></span>
                {if user_has_perm('users_edit')}
                    <a href="#" class="tfa" data-url="{$CAT_ADMIN_URL}/users/tfa" data-id="{$user.user_id}" title="{translate('Two factor authentication is')}: {if $user.tfa_enabled == 'N'}{translate('disabled')}{else}{translate('enabled')}{/if}"><span class="text-success fa fa-fw fa-{if $user.tfa_enabled == 'N'}un{/if}lock {if $user.tfa_enabled == 'N'}yellow{/if}"></span></a>
                    {if $user.protected != 'Y'}
                    <a href="#" class="bsedit" data-url="{$CAT_ADMIN_URL}/users/edit" data-id="{$user.user_id}" title="{translate('Edit')}"><span class="fa fa-fw fa-pencil"></span></a>
                    {else}
                    <span class="fa fa-fw"></span>
                    {/if}
                {else}
                    <span class="fa fa-fw"></span><span class="fa fa-fw"></span>
                {/if}
                {if $user.protected != 'Y'}
                {if user_has_perm('users_delete')}<a href="#" class="delete" data-url="{$CAT_ADMIN_URL}/users/delete" data-id="{$user.user_id}" data-name="{$user.username}" title="{translate('Delete')}"><span class="fa fa-fw fa-trash text-danger"></span></a>{else}<span class="fa fa-fw"></span>{/if}
                {/if}
            </td>
            <td>{$user.user_id}</td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="title" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} title="{translate('Username')}">{$user.username}</a></td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="display_name" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} title="{translate('Display name')}">{$user.display_name}</a></td>
            <td><a href="#" {if user_has_perm('users_edit')}class="editable" data-name="email" data-type="text" data-pk="{$user.user_id}" data-url="{$CAT_ADMIN_URL}/users/edit"{/if} title="{translate('eMail')}">{$user.email}</a></td>
            <td>
                {if user_has_perm('users_edit') && count($user.groups)}
                <div class="pillbox" data-initialize="pillbox" id="groupList">
                    <ul class="clearfix pill-group">
                        {foreach $user.groups group}
                        <li class="btn btn-default btn-sm pill" data-value="foo">
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
            <td>{$user.wysiwyg}</td>
        </tr>
    {/foreach}
    </tbody>
</table>

<small>
<span class="fa fa-fw fa-anchor"></span> = {translate('Built in')}<br />
<span class="fa fa-fw fa-pencil"></span> = {translate('Edit')}<br />
<span class="text-success fa fa-fw fa-lock"></span> = {translate('Two-Step Authentication enabled')}<br />
<span class="text-success fa fa-fw fa-unlock"></span> = {translate('Two-Step Authentication disabled')}<br />
<span class="fa fa-fw fa-trash text-danger"></span> = {translate('Delete')}
</small><br /><br />

{* hidden user form *}
<div style="display:none">
    {$userform}
</div>

{include(file='backend_modal.tpl' modal_id='modal_dialog' modal_title='', modal_text='', modal_savebtn='1')}

{$file = cat('modules/lib_javascript/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
<script type="text/javascript">
//<![CDATA[
    $(function() {
        CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
    });
//]]>
</script>