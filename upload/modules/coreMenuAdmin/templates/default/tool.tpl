    <div id="coreMenuAdmin">
        <h2>{translate('BlackCat Menu Manager')}</h2>
        {if is_array($menus)}
        <table class="table">
            <thead>
                <tr>
                    <th colspan="3">{translate('Available menus')}</th>
                </tr>
                <tr>
                    <th></th>
                    <th>{translate('Markup')}</th>
                    <th>{translate('Menu type')}</th>
                    <th>{translate('Info')}</th>
                    <th>{translate('Actions')}</th>
                </tr>
            </thead>
            <tbody>
        {foreach $menus m}
                <tr>
                    <td>{if $m.core == 'Y'}<span class="fa fa-fw fa-anchor" title="{translate('Built-in')}"></span>{/if}</td>
                    <td><tt>&#123;menu({$m.menu_id})&#125;</td>
                    <td>{translate($m.type_name)}</td>
                    <td>{translate($m.info)}</td>
                    <td>
                        {if $m.protected == 'Y'}
                        <i>{translate('protected')}</i>
                        {else}{if user_has_perm('menu_edit')}
                        <a href="{$CAT_ADMIN_URL}/admintools/tool/coreMenuAdmin/edit/{$m.menu_id}">{translate('Edit')}</a>
                        {/if}
                    </td>
                </tr>
        {/foreach}
            </tbody>
        </table>
        {else}
        <div class="alert alert-info">
          {translate('There are no menus yet')}
        </div>
        {/if}

        <form method="GET" action="{$CAT_ADMIN_URL}/admintools/tool/coreMenuAdmin/analyze">
        <button type="submit">{translate('Analyze HTML')}</button>
        </form>
    </div>
