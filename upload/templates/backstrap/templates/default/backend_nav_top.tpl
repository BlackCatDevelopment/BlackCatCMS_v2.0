{template dropdownmenu data}
          <ul class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
            {foreach $data item}
            <li{if $item.children} class="dropdown-submenu"{/if}>
              <a class="dropdown-item{if $item.children} dropdown-toggle{/if}" href="{$item.href}"{if $item.children} data-toggle="dropdown"{/if}>{$item.title}</a>
              {if $item.children}{dropdownmenu $item.children}{/if}
            </li>
            {/foreach}
          </ul>
{/template}
{template topmenu data}
        {foreach $data item}{if $item.name != 'preferences' && $item.name != 'page' && $item != 1}
        <li class="nav-item{if $item.children} dropdown{/if}{if $item.is_current || $item.is_in_trail} active{/if}">
          <a href="{$item.href}" class="nav-link{if $item.children} dropdown-toggle{/if}"{if $item.children} data-toggle="dropdown" aria-has-popup="true" aria-expanded="false"{/if}>
            <i class="fa fa-fw fa-{$item.name}"></i>
            {translate($item.title)}
          </a>
          {if $item.children}{dropdownmenu $item.children}{/if}
        </li>{/if}{/foreach}
{/template}
  <header>
    <nav class="navbar navbar-expand-md navbar-dark fixed-top bg-dark" id="bsTop">
      <a class="navbar-brand" href="{$CAT_ADMIN_URL}/dashboard">BlackCat CMS {$CAT_VERSION}</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarMainContent" aria-controls="navbarMainContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="navbar-collapse collapse" id="navbarMainContent">
        <ul class="navbar-nav mr-auto">
          {topmenu $MAIN_MENU_RECURSIVE}
        </ul>
        <ul class="navbar-nav flex-row ml-md-auto d-none d-md-flex">
          <li class="nav-item dropdown">
            <a class="nav-item nav-link dropdown-toggle mr-md-2" id="bsUserDropdown" data-toggle="dropdown" title="{$meta.USER.display_name}" href="#" aria-haspopup="true" aria-expanded="false">
              <i class="fa fa-user fa-fw"></i> {$meta.USER.display_name}
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="bsUserDropdown">
              <a href="#" class="dropdown-item active"><i class="fa fa-user fa-fw"></i> {translate('User Profile')}</a>
              <a href="{$CAT_ADMIN_URL}/logout/" class="dropdown-item"><i class="fa fa-sign-out fa-fw"></i> {translate('Logout')}</a>
            </div>
          </li>
        </ul>{* /.navbar-top-links *}
      </div>{* /.navbar-collapse *}
    </nav>
    <nav aria-label="breadcrumb" role="navigation" class="mt50" id="bsBreadcrumb">
      <ol class="breadcrumb">
        <li class="breadcrumb-item{if $meta.SECTION} active" aria-current="page"{else}"{/if}><a href="{$CAT_ADMIN_URL}">{translate('Home')}</a></li>
        {if $meta.SECTION}<li class="breadcrumb-item{if ! $meta.ACTION} active" aria-current="page"{else}"{/if}><a href="{$CAT_ADMIN_URL}/{$meta.SECTION}">{translate($meta.SECTION)}</a></li>{/if}
        {if $meta.ACTION}<li class="breadcrumb-item active" aria-current="page"><a href="{$CAT_ADMIN_URL}/{$meta.SECTION}/{$meta.ACTION}">{translate($meta.ACTION)}</a></li>{/if}
      </ol>
    </nav>
  </header>