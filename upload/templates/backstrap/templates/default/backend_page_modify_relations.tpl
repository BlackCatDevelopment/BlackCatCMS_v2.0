        {if $linked}
        <table class="table">
          <thead>
            <tr>
              <th></th>
              <th>{translate('Language')}</th>
              <th>{translate('Linked page')}</th>
              <th>{translate('Last modified')}</th>
            </tr>
          </thead>
          <tbody>
          {foreach $linked item}
          <tr>
            <td><span class="fa fa-fw fa-chain-broken" data-id="{$item.page_id}"></span></td>
            <td>{$item.lang}</td>
            <td>{$item.menu_title} (ID: {$item.page_id})</td>
            <td>{cat_format_date($item.modified_when)}</td>
          </tr>
          {/foreach}
          </tbody>
        </table>
        {/if}
        {translate('You can link any page to other pages in different languages that have the same content.')}
        {translate('Use {language_menu()} in your frontend template to show links to the pages listed below.')}