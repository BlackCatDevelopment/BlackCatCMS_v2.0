<ul class="nav nav-tabs nav-fill">
    <li class="nav-item">
        <a class="nav-link{if $current=="installed"} active{/if}" href="{$CAT_ADMIN_URL}/addons/index">
            <span class="fa fa-fw fa-check-square-o"></span>
            {translate('Installed')}
        </a>
    </li>
    {if user_has_perm('addons_install')}
    <li class="nav-item">
        <a class="nav-link{if $current=="catalog"} active{/if}" href="{$CAT_ADMIN_URL}/addons/catalog">
          <span class="fa fa-fw fa-bars"></span>
          {translate('Catalog')}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{if $current=="notinstalled"} active{/if}" href="{$CAT_ADMIN_URL}/addons/notinstalled">
          <span class="fa fa-fw fa-hdd-o"></span>
          {translate('Not (yet) installed')}
        </a>
    </li>
    {/if}
    {if user_has_perm('addons_create')}
    <li class="nav-item">
        <a class="nav-link{if $current=="create"} active{/if}" href="{$CAT_ADMIN_URL}/addons/create">
          <span class="fa fa-fw fa-plus-square"></span>
          {translate('Create new')}
        </a>
    </li>
    {/if}
</ul>