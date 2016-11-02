{template topmenu data}
        {foreach $data item}{if $item.name != 'preferences' && $item.name != 'pages' && $item != 1}
        <li{if $item.children} class="dropdown{if $item.level>0}-submenu{/if}{if $item.is_current || $item.is_in_trail} active{/if}"{/if}>
          <a href="{$item.href}"{if $item.children} class="dropdown-toggle" data-toggle="dropdown" aria-has-popup="true" aria-expanded="false"{/if}>
            <i class="fa fa-fw fa-{$item.name}"></i>
            {translate($item.title)}
            {if $item.children}<i class="fa fa-caret-{if $item.level>0}right{else}down{/if}"></i>{/if}
          </a>
          {if $item.children}
          <ul class="dropdown-menu">
            {topmenu $item.children}
          </ul>
          {/if}
        </li>{/if}{/foreach}
{/template}
<div class="container-fluid">
  <div class="row">

    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
    
        <div class="navbar-header col-sm-3 col-md-2">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">{translate('Toggle navigation')}</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="{$CAT_ADMIN_URL}/dashboard">BlackCat CMS {$CAT_VERSION}</a>
        </div>
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            {topmenu $MAIN_MENU_RECURSIVE}
          </ul>
          <ul class="nav navbar-right navbar-nav">
            <li id="contextual" class="dropdown hidden">
              <a class="dropdown-toggle" data-toggle="dropdown" title="" href="#">
                <i class="fa fa-cogs fa-fw"></i> {translate('Context menu')} <i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu">
              </ul>
            </li>
            <li class="dropdown">
              <a class="dropdown-toggle" data-toggle="dropdown" title="{$USER.display_name}" href="#">
                <i class="fa fa-user fa-fw"></i> {$USER.display_name} <i class="fa fa-caret-down"></i>
              </a>
              <ul class="dropdown-menu dropdown-user">
                <li><a href="#"><i class="fa fa-user fa-fw"></i> {translate('User Profile')}</a></li>
                <li class="divider"></li>
                <li><a href="{$CAT_ADMIN_URL}/logout/"><i class="fa fa-sign-out fa-fw"></i> {translate('Logout')}</a></li>
              </ul>
            </li>
          </ul><!-- /.navbar-top-links -->
          </div><!-- /.navbar-collapse -->
  </nav>

  </div><!-- /.row -->
</div><!-- /.container-fluid -->

