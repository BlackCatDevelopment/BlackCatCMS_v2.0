<div class="alert alert-info">
    {translate('These are the <strong>globally</strong> available social media services.')}
    {translate('Services added here are available for all sites.')}
    {translate('Services deleted here will no longer be available on all sites. (!)')}
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
    {translate('The services can be configured using the "Social Media Services" Admin Tool.')}
</div>

<table class="table table-striped table-hover table-condensed">
    <tbody>
{foreach $services item}
        <tr>
            <td rowspan="2">
                {if user_has_perm('socialmedia_delete')}<a href="{$CAT_ADMIN_URL}/socialmedia/delete/{$item.id}"><i class="fa fa-fw fa-trash text-danger" title="{translate('Delete service')}"></i></a>{/if}
                {$item.name}
            </td>
            <td><span class="label">{translate('"Follow us" URL')}</span></td>
            <td>
                <a href="#" class="editable" data-name="follow_url" data-type="text" data-pk="{$item.id}" data-url="{$CAT_ADMIN_URL}/socialmedia/edit" title="{translate('&quot;Follow&quot; URL')}">{$item.follow_url}</a>
            </td>
        </tr>
        <tr>
            <td><span class="label">{translate('"Share this" URL')}</span></td>
            <td>
                <a href="#" class="editable" data-name="share_url" data-type="text" data-pk="{$item.id}" data-url="{$CAT_ADMIN_URL}/socialmedia/edit" title="{translate('&quot;Share&quot; URL')}">{$item.share_url}</a>
            </td>
        </tr>
{/foreach}
    </tbody>
</table>

{if user_has_perm('socialmedia_add')}
<h3>{translate('Add service')}</h3>
<form role="form" method="post" class="form-inline ajax" action="{$CAT_ADMIN_URL}/socialmedia/add">
    <label class="sr-only" for="socialmedia_name">{translate('Name')}</label>
        <input type="text" placeholder="{translate('Name')}" class="form-control mb-2 mr-sm-2 mb-sm-0" id="socialmedia_name" name="socialmedia_name" required="required" />
    <input type="submit" class="btn btn-primary" />
    <input type="reset" class="btn btn-link" type="button" />
</form>
{/if}

<br /><br /><br />