{template submenu tpldata}
          <ul class="flex-column pl-3 nav">
          {foreach $tpldata item}
            <li class="nav-item">
              <span class="nav-link{if $item.children} arrow{/if}"{if $item.children} data-toggle="collapse" data-target="#sub-{$item.page_id}"{/if}>
                {$item.menu_title}
                <a href="{$_root.CAT_ADMIN_URL}/page/edit/{$item.page_id}"><i class="fa fa-fw fa-edit"></i></a>
              </span>
              {if $item.children}
                <div class="collapse" id="sub-{$item.page_id}" aria-expanded="false">
                {submenu $item.children}
                </div>
              {/if}
            </li>
          {/foreach}
          </ul>
{/template}

{template pagetree tpldata}
  {foreach $tpldata item}
      <li class="nav-item">
        <span class="nav-link{if $item.children} arrow{/if}"{if $item.children} data-toggle="collapse" data-target="#sub-{$item.page_id}"{/if}>
          {$item.menu_title}
          <a href="{$__.CAT_ADMIN_URL}/page/edit/{$item.page_id}"><i class="fa fa-fw fa-edit"></i></a>
        </span>
        {if $item.children}
        <div class="collapse" id="sub-{$item.page_id}" aria-expanded="false">
        {submenu $item.children}
        </div>
        {/if}
      </li>
  {/foreach}
{/template}

    <div class="col-2 collapse d-md-flex bg-light pt-2 h-100" id="sidebar">
      <ul class="nav flex-column flex-nowrap">
      {pagetree $pages}
      </ul>
    </div>
