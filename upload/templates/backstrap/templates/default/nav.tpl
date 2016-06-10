        <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="sr-only">{translate('Toggle navigation')}</span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="{$CAT_ADMIN_URL}/dashboard">BlackCat CMS {$CAT_VERSION}</a>
            </div>
            <!-- /.navbar-header -->

            {* <div class="navbar-title">{$SECTION}</div> *}

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
                    <!-- /.dropdown-user -->
                </li>
                <!-- /.dropdown -->
            </ul>
            <!-- /.navbar-top-links -->

            <div class="navbar-default sidebar" role="navigation">
                <div class="sidebar-nav navbar-collapse">
                    <ul class="nav" id="side-menu">
                        {foreach $MAIN_MENU as menu}{if $menu.name != 'preferences'}
                        <li>
        					<a href="{$menu.link}" target="_self"><span class="fa fa-fw fa-{$menu.name}"></span> {$menu.title}</a>
        				</li>
                        {/if}{/foreach}
                    </ul>
                </div>
                <!-- /.sidebar-collapse -->
            </div>
            <!-- /.navbar-static-side -->
        </nav>