<ul class="nav nav-tabs nav-fill">
    <li class="nav-item">
        <a class="nav-link{if $current=="folders"} active{/if}" href="{$CAT_ADMIN_URL}/media/index">
            <span class="fa fa-fw fa-folder"></span>
            {translate('Folders')}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link{if $current=="files"} active{/if}" href="{$CAT_ADMIN_URL}/media/files">
          <span class="fa fa-fw fa-bars"></span>
          {translate('Files')}
        </a>
    </li>
    {if user_has_perm('media_upload')}
    <li class="nav-item">
        <a class="nav-link" href="{$CAT_ADMIN_URL}/media/upload">
          <span class="fa fa-fw fa-upload"></span>
          {translate('Upload')}
        </a>
    </li>
    {/if}
</ul>