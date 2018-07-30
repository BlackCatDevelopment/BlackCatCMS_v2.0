{template submenu tpldata}
          <ul>
          {foreach $tpldata item}
            <li>
              <a href="{$_root.CAT_ADMIN_URL}/page/edit/{$item.page_id}"><span class="pagename">{$item.menu_title}</span></a>
              <a class="bsAddPage" data-parent="{$item.page_id}" href="{$_root.CAT_ADMIN_URL}/page/add/{$item.page_id}"><i class="fa fa-fw fa-plus"></i></a>
              {if $item.children}
                {submenu $item.children}
              {/if}
            </li>
          {/foreach}
          </ul>
{/template}

{template pagetree tpldata}
  {foreach $tpldata item}
      <li>
        <a href="{$_root.CAT_ADMIN_URL}/page/edit/{$item.page_id}"><span class="pagename">{$item.menu_title}</span></a>
        <a class="bsAddPage" data-parent="{$item.page_id}" href="{$_root.CAT_ADMIN_URL}/page/add/{$item.page_id}"><i class="fa fa-fw fa-plus"></i></a>
        {if $item.children}
        {submenu $item.children}
        {/if}
      </li>
  {/foreach}
{/template}

    <div class="col-2 pt-2 h-100" id="sidebar">
      <input class="form-control" id="bsPageSearch" placeholder="Search..." autocomplete="off" type="search" /><br />
      <ul class="nav nav-pills nav-fill">
        <li class="nav-item">
          <a class="nav-link active bsAddPage" data-parent="0" href="#" title="{translate('Add page on top')}"><i class="fa fa-fw fa-plus"></i></a>
        </li>
      </ul>
      <ul class="nav flex-column flex-nowrap treed folder openAll">
      {pagetree $pages}
      </ul>
      <ul class="nav nav-pills nav-fill">
        <li class="nav-item">
          <a class="nav-link active bsAddPage" data-parent="0" href="#" title="{translate('Add page at bottom')}"><i class="fa fa-fw fa-plus"></i></a>
        </li>
      </ul>

    </div>
