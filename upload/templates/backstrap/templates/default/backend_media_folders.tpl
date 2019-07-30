{include file="backend_media_tabs.tpl"}
{if $dirs}
        <table class="table">
            <thead><tr><th>{translate('Foldername')}</th><th>{translate('Actions')}</th></tr></thead>
            <tbody>
{foreach $dirs item}
            <tr>
                <td class="pl-{$item.level}{if $item.deleted == 1} deleted{/if}">{if ! $item.name}[Root]{else}{if $item.level > 1}<i class="fa fa-fw fa-long-arrow-right"></i>{/if}{$item.name}{/if}</td>
                <td>
                    {if $item.protected == 1}<a href="{$CAT_ADMIN_URL}/media/unprotect/{$item.path}"><i class="fa fa-fw fa-lock text-primary"></i></a>{else}<a href="{$CAT_ADMIN_URL}/media/protect/{$item.path}"><i class="fa fa-fw fa-unlock text-success"></i></a>{/if}
                    {if $item.deleted == 1}<a href="#"><i class="fa fa-fw fa-life-saver"></i></a>{/if}
                    <a href=""{$CAT_ADMIN_URL}/media/delete/{$item.path}" class="folder-delete" data-id="{$item.dir_id}" data-name="{$item.name}"><i class="fa fa-fw fa-trash text-danger"></i></a>
                </td>
            </tr>
{/foreach}
            </tbody>
        </table>
{else}
        <div class="alert alert-info">
            {translate('No folders yet')}
        </div>
{/if}
        <form method="GET" action="{$CAT_ADMIN_URL}/media/update">
            <button class="btn btn-primary">{translate('Scan for updates')}</button>
            <span class="text-muted">{translate('If you have added, moved or deleted files or folders by FTP, click this button to update the database.')}</span>
        </form>
