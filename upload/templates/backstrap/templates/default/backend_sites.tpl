        <table class="table">
            <thead>
                <tr>
                    <th>{* icons column *}</th>
                    <th>{translate('Site ID')}</th>
                    <th>{translate('Name')}</th>
                    <th>{translate('Basedir')}</th>
                    <th>{translate('Subfolder')}</th>
                    <th>{translate('Size')}</th>
                    {if user_has_perm('users_list')}<th>{translate('Owner')}</th>{/if}
                </tr>
            </thead>
            <tbody>
            {foreach $sites site}
                <tr>
                    <td>
                    </td>
                    <td>{$site.site_id}</td>
                    <td>{$site.site_name}</td>
                    <td>{if $site.site_basedir}{$site.site_basedir}{else}<i>default</i>{/if}</td>
                    <td>{$site.site_folder}</td>
                    <td>{$site.asset_size}</td>
                    {if user_has_perm('users_list')}<td>{$site.username}</td>{/if}
                </tr>
            {/foreach}
            </tbody>
        </table>

{if user_has_perm('sites_add')}
    <div id="bsSiteForm">
    {$new_site_form}
    </div>
{/if}