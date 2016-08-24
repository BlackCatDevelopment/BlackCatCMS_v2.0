        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">{translate('Toggle navigation')}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{$CAT_ADMIN_URL}/dashboard">BlackCat CMS {$CAT_VERSION}</a>
            </div><!-- /.navbar-header -->

            <ul class="nav nav-pills navbar-top-links navbar-right">
                <li class="dropdown">
                    <a class="dropdown-toggle" data-toggle="dropdown" title="{$USER.display_name}" href="#">
                        <i class="fa fa-user fa-fw"></i> <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-user">
                        <li><a href="#"><i class="fa fa-user fa-fw"></i> {translate('User Profile')}</a></li>
                        <li class="divider"></li>
                        <li><a href="{$CAT_ADMIN_URL}/logout/"><i class="fa fa-sign-out fa-fw"></i> {translate('Logout')}</a></li>
                    </ul>
                </li>
            </ul><!-- /.navbar-top-links -->

            {template sidemenu data}
                {foreach $data item}{if $item.name != 'preferences' && $item != 1}
                <li>
                    <a href="{$item.href}"{if $item.is_current || $item.is_in_trail} class="active"{/if}><i class="fa fa-fw fa-{$item.name}"></i> {translate($item.title)}{if $item.children}<span class="fa arrow"></span>{/if}</a>
                    {if $item.children && $item.is_in_trail}
                    <ul class="nav nav-level-{$item.level + 1}">
                        {sidemenu $item.children}
                    </ul>
                    {/if}
                </li>
                {/if}{/foreach}
            {/template}

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        {sidemenu $MAIN_MENU_RECURSIVE}
                    </ul>
                </div>
            </div><!-- /.navbar-static-side -->
        </nav>
