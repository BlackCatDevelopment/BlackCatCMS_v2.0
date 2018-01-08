<ul class="nav nav-tabs nav-fill" role="tablist">{* Tabs *}
    <li class="nav-item">
        <a class="nav-link" href="#folders" aria-controls="folders" role="tab" data-toggle="tab" data-url="{$CAT_ADMIN_URL}/media/index">
          <span class="fa fa-fw fa-folder"></span>
          {translate('Folders')}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#list" aria-controls="list" role="tab" data-toggle="tab" data-url="{$CAT_ADMIN_URL}/media/index">
          <span class="fa fa-fw fa-bars"></span>
          {translate('List')}
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="#grid" aria-controls="grid" role="tab" data-toggle="tab" data-url="{$CAT_ADMIN_URL}/media/index">
          <span class="fa fa-fw fa-th"></span>
          {translate('Grid')}
        </a>
    </li>
    {if user_has_perm('media_upload')}
    <li class="nav-item">
        <a class="nav-link" href="#upload" aria-controls="upload" role="tab" data-toggle="tab">
          <span class="fa fa-fw fa-upload"></span>
          {translate('Upload')}
        </a>
    </li>
    {/if}
    {if count($dirs)}
    <li class="nav-item">
        <span class="fa fa-fw fa-folder"></span>
        <label for="media_folder">{translate('Select folder')}:</label>
        <select id="root_folder" name="root_folder">
            <option value="">[{translate('Root folder')}]</option>
            {foreach $dirs item}
            <option value="{$item.path}"{if $__.curr_folder == $item.path} selected="selected"{/if}>{$item.path}</option>
            {/foreach}
        </select>
    </li>
    {/if}
</ul>

<div class="tab-content">{* Tab panes *}

{* -------------------- START #folders tab-pane -------------------- *}
      <div role="tabpanel" class="tab-pane" id="folders">
        {* the content will be loaded via AJAX *}
        <div class="fa fa-fw fa-3x text-center" style="width:100%"></div><span class="sr-only">Loading...</span>
        <div class="alert alert-danger" style="display:none;">
            {translate('Unable to load the tab!')}
        </div>
        {include file="backend_media_folderstab.tpl"}
      </div>{* END #folders tab-pane *}

{* -------------------- START #list tab-pane -------------------- *}
      <div role="tabpanel" class="tab-pane" id="list">
        {* the content will be loaded via AJAX *}
        <div class="fa fa-fw fa-3x text-center" style="width:100%"></div><span class="sr-only">Loading...</span>
        <div class="alert alert-danger" style="display:none;">
            {translate('Unable to load the tab!')}
        </div>
        {include file="backend_media_listtab.tpl"}
      </div>{* END #list tab-pane *}

{* -------------------- START #grid tab-pane -------------------- *}
      <div role="tabpanel" class="tab-pane" id="grid">
        {* the content will be loaded via AJAX *}
        <div class="fa fa-fw fa-3x text-center" style="width:100%"></div><span class="sr-only">Loading...</span>
        <div class="alert alert-danger" style="display:none;">
            {translate('Unable to load the tab!')}
        </div>
        {include file="backend_media_gridtab.tpl"}
      </div>{* END #grid tab-pane *}

{* -------------------- START #upload tab-pane -------------------- *}
      <div role="tabpanel" class="tab-pane" id="upload">
        {include file="backend_media_uploadtab.tpl"}
      </div>{* END #grid tab-pane *}

</div>{* Tab panes End *}

{* will be cloned and added to list and grid view *}
<span class="bsFilterSelect float-right" style="display:none">
    <label for="filter">{translate('Filter by type')}</label>
    <select name="filter">
        <option value="">{translate('All')}</option>
        <option value="image">{translate('Images')}</option>
        <option value="video">{translate('Videos')}</option>
        <option value="audio">{translate('Audio')}</option>
        <option value="other">{translate('Other')}</option>
    </select>
</span>

{* get the name of the language file; allows to check if it exists *}
{$file = cat('modules/lib_javascript/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
{* set javascript var for later use *}
<script type="text/javascript">
//<![CDATA[
    var CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
//]]>
</script>
