{template pagetree pages}
    {foreach $pages item}
            <div class="list-group-item{if $item.children} collapsed" data-toggle="collapse" data-target="#bsMenu{$item.page_id}" aria-expanded="false"{else} leaf"{/if}>
                <span class="list-group-item-actions">
                    <a href="{$_root.CAT_ADMIN_URL}/page/edit/{$item.page_id}" class="list-group-link" title="{translate('Edit page')}"><i class="fa fa-cogs"></i></a>
                    <a href="{$_root.CAT_ADMIN_URL}/page/add/{$item.page_id}" class="list-group-link bsAddPage" title="{translate('Add page before')}" data-id="{$item.page_id}" data-parent="{$item.parent}" data-pos="before"><i class="fa fa-hand-o-up"></i></a>
                    <a href="{$_root.CAT_ADMIN_URL}/page/add/{$item.page_id}" class="list-group-link bsAddPage" title="{translate('Add page below')}" data-id="{$item.page_id}" data-parent="{$item.parent}" data-pos="after"><i class="fa fa-hand-o-down"></i></a>
                </span>
                <span class="hidden-sm-down pagename">{$item.menu_title}</span>
            </div>
            {if $item.children}
            <div class="collapse" id="bsMenu{$item.page_id}">
            {pagetree $item.children}
            </div>
            {/if}
    {/foreach}
{/template}


    <div class="col-md-2 col-xs-1 p-l-0 p-r-0 collapse show" id="sidebar">
        <input class="form-control" id="bsPageSearch" placeholder="Search..." autocomplete="off" type="search" /><br />
        <div class="list-group panel">
        {pagetree $pages}
        </div>
    </div>

    <div data-toggle="collapse" data-target="#sidebar" id="sidebar-closer" class="bg-dark">
    </div>

