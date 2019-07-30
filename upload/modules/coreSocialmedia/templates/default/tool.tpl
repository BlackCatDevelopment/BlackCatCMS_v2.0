<div class="alert alert-info">
    {translate('These are the available social media services. You can enable or disable each link for all pages here. In addition, you may override these settings for each individual page.')}<br />
    {translate('Placeholders')}:<br />
    <ul>
        <li>
            &#123;$ACCOUNT&#125; - {translate('Will be replaced with the name of the configured service account')}
        </li>
        <li>
            &#123;$PAGE_URL&#125; - {translate('Will be replaced with the URL of the current page')}
        </li>
        <li>
            &#123;$PAGE_TITLE&#125; - {translate('Will be replaced with the page title of the current page')}
        </li>
        <li>
            &#123;$DESCRIPTION&#125; - {translate('Will be replaced with the description META information of the current page')}
        </li>
    </ul>
</div>

<table class="table table-striped table-hover table-condensed">
    <tbody>
{foreach $services item}
        <tr>
            <td rowspan="3">
                {if user_has_perm('socialmedia_delete')}<a href="{$CAT_ADMIN_URL}/admintools/tool/coreSocialmedia/delete/{$item.id}"><i class="fa fa-fw fa-trash text-danger" title="{translate('Delete service')}"></i></a>{/if}
                {$item.name}
            </td>
            <td><label for="socialmedia_account_{$item.id}">{translate('Account')}</label></td>
            <td></td>
            <td>{if user_has_perm('socialmedia_edit')}
                <a href="#" class="editable" data-name="account" data-type="text" data-pk="{$item.id}" data-url="{$CAT_ADMIN_URL}/admintools/tool/coreSocialmedia/edit" title="{translate('Accountname')}">{$item.account}</a>
                {else}
                {$item.account}
                {/if}
            </td>
        </tr>
        <tr>
            <td><span class="label">{translate('"Follow us" URL')}</span></td>
            <td>{if $item.follow_url && user_has_perm('socialmedia_edit')}
                <label class="form-check-label custom-control custom-checkbox">
                    <input name="socialmedia_follow_enabled_{$item.id}" id="socialmedia_follow_enabled_{$item.id}" class="form-check-input custom-control-input" type="checkbox" value="Y"{if ! $item.follow_disabled=="Y"} checked="checked"{/if} />
                    <span class="custom-control-indicator" title="{translate('Enable/disable for whole site')}"></span>
                </label>{else}<span style="display:inline-block;width:4.2em;"></span>{/if}
            </td>
            <td>
                <a href="#" class="editable" data-name="follow_url" data-type="text" data-pk="{$item.id}" data-url="{$CAT_ADMIN_URL}/admintools/tool/coreSocialmedia/edit" title="{translate('&quot;Follow&quot; URL')}">{$item.follow_url}</a>
            </td>
        </tr>
        <tr>
            <td><span class="label">{translate('"Share this" URL')}</span></td>
            <td>{if $item.share_url && user_has_perm('socialmedia_edit')}
                <label class="form-check-label custom-control custom-checkbox">
                    <input name="socialmedia_share_enabled_{$item.id}" id="socialmedia_share_enabled_{$item.id}" class="form-check-input custom-control-input" type="checkbox" value="Y"{if ! $item.share_disabled=="Y"} checked="checked"{/if} />
                    <span class="custom-control-indicator" title="{translate('Enable/disable for whole site')}"></span>
                </label>{else}<span style="display:inline-block;width:4.2em;"></span>{/if}
            </td>
            <td>
                <a href="#" class="editable" data-name="share_url" data-type="text" data-pk="{$item.id}" data-url="{$CAT_ADMIN_URL}/admintools/tool/coreSocialmedia/edit" title="{translate('&quot;Share&quot; URL')}">{$item.share_url}</a>
            </td>
        </tr>
{/foreach}
    </tbody>
</table>