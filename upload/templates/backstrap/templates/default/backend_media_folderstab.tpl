{if $dirs}
        <table class="table">
            <thead><tr><th>{translate('Foldername')}</th><th>{translate('Actions')}</th></tr></thead>
            <tbody>
{foreach $dirs item}
            <tr>
                <td class="pl-{$item.level}{if $item.deleted == 1} deleted{/if}">{if ! $item.name}[Root]{else}{if $item.level > 1}<i class="fa fa-fw fa-long-arrow-right"></i>{/if}{$item.name}{/if}</td>
                <td>
                    {if $item.protected == 1}<a href="{$CAT_ADMIN_URL}/media/unprotect/{$item.path}"><i class="fa fa-fw fa-lock text-primary"></i></a>{else}<a href="{$CAT_ADMIN_URL}/media/protect/{$item.path}"><i class="fa fa-fw fa-unlock text-success"></i></a>{/if}
                    {if $item.deleted == 1}<a href="#"><i class="fa fa-fw fa-life-saver"></i></a>{else}<a href="#"><i class="fa fa-fw fa-trash text-danger"></i></a>{/if}
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
        <br /><br />
