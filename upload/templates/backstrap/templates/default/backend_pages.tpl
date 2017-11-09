{template pagetree data}
    {foreach $data item}
    <li class="route">
      <div class="card">
        <div class="panel-body">
          <div class="handler"><i class="fa fa-fw fa-arrows"></i></div>
          <div>{$item.menu_title}</div>
          <div class="icons">
            <div><a href="#" id="bs-page-visibility-{$item.page_id}" class="bs-page-visibility" data-type="select" data-pk="1" data-url="/post" data-value="{$item.visibility}" title="{translate('Visibility')}"><i class="fa fa-fw fa-{$item.visibility}" title="{translate('Visibility')}: {translate($item.visibility)}"></i></a></div>
            <div><a href="{$_root.CAT_ADMIN_URL}/page/edit/{$item.page_id}" title="{translate('Edit content')}"><i class="fa fa-fw fa-pencil"></i></a></div>
          </div>
        </div>
      </div>
      <ul class="space" id="space1">
          {if $item.children}
            {pagetree $item.children}
          {/if}
      </ul>
    </li>
    {/foreach}
{/template}

    <div class="treecontainer">
{if $pages}
        <ul class="list-group space first-space" id="space0">
            {pagetree $pages}
        </ul>
{else}
        <div class="alert alert-info">
            {translate('No pages yet')}
        </div>
{/if}
        <br /><br />
        <a href="{$_root.CAT_ADMIN_URL}/page/add" class="bsAddPage btn btn-primary">{translate('Add page')}</a>
    </div><br /><br />

    <div class="legend"><strong>{translate('Visibility')}:</strong>
        <i class="fa fa-fw fa-public"></i> <span title="{translate('The page is accessible for all visitors and shows up in the navigation by default')}">{translate('public')}</span>
        <i class="fa fa-fw fa-hidden"></i> <span title="{translate('The page is accessible for visitors who know the exact address and can be found by the keyword search, but does not show up in the navigation by default')}">{translate('hidden')}</span>
        <i class="fa fa-fw fa-private"></i> <span title="{translate('?')}">{translate('private')}</span>
        <i class="fa fa-fw fa-registered"></i> <span title="{translate('The page is only accessible to registered users and is not shown in the navigation for non-registered users')}">{translate('registered')}</span>
        <i class="fa fa-fw fa-none"></i> <span title="{translate('The page is not accessible in the frontend at all, but can be edited in the backend')}">{translate('none')}</span>
        <i class="fa fa-fw fa-deleted"></i> <span title="{translate('The page was deleted but can be recovered')}">{translate('deleted')}</span>
        <i class="fa fa-fw fa-draft"></i> <span title="{translate('The page is not ready yet and is not shown in the frontend')}">{translate('draft')}</span>
    </div>

</div>