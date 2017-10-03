{template pagetree data}
    {foreach $data item}
    <li class="route">
      <div class="panel panel-default">
        <div class="panel-body">
          <div class="handler"><i class="fa fa-fw fa-arrows"></i></div>
          <div>{$item.menu_title}</div>
          <div class="icons">
            <div><a href="#" id="bs-page-visibility-{$item.page_id}" class="bs-page-visibility" data-type="select" data-pk="1" data-url="/post" data-value="{$item.visibility}" data-title="{translate('Visibility')}"><i class="fa fa-fw fa-{$item.visibility}" title="{translate('Visibility')}: {translate($item.visibility)}"></i></a></div>
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
        <a href="{$_root.CAT_ADMIN_URL}/page/add" class="add btn btn-primary">{translate('Add page')}</a>
    </div><br /><br />

    <div class="legend"><strong>{translate('Visibility')}:</strong>
        <i class="fa fa-fw fa-public"></i> {translate('public')}
        <i class="fa fa-fw fa-hidden"></i> {translate('hidden')}
        <i class="fa fa-fw fa-private"></i> {translate('private')}
        <i class="fa fa-fw fa-registered"></i> {translate('registered')}
        <i class="fa fa-fw fa-none"></i> {translate('none')}
        <i class="fa fa-fw fa-deleted"></i> {translate('deleted')}
    </div>

</div>

{include(file='backend_modal.tpl' modal_id='modal_dialog' modal_title='', modal_text='$add_page_form', modal_savebtn='1')}