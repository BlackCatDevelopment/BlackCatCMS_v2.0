<ul class="nav nav-tabs" role="tablist">{* Tabs *}
    <li role="presentation">
        <a href="#list" aria-controls="list" role="tab" data-toggle="tab" data-url="{$CAT_ADMIN_URL}/media/index">
          <span class="fa fa-fw fa-bars"></span>
          {translate('List')}
        </a>
    </li>
    <li role="presentation">
        <a href="#grid" aria-controls="grid" role="tab" data-toggle="tab" data-url="{$CAT_ADMIN_URL}/media/index">
          <span class="fa fa-fw fa-th"></span>
          {translate('Grid')}
        </a>
    </li>
    {if user_has_perm('media_upload')}
    <li role="presentation">
        <a href="#upload" aria-controls="upload" role="tab" data-toggle="tab">
          <span class="fa fa-fw fa-upload"></span>
          {translate('Upload')}
        </a>
    </li>
    {/if}
    {if count($dirs)}
    <li>
        <span class="fa fa-fw fa-folder"></span>
        <label for="media_folder">{translate('Select folder')}:</label>
        <select id="root_folder" name="root_folder">
            <option value="">[{translate('Root folder')}]</option>
            {foreach $dirs item}
            <option value="{$item}"{if $__.curr_folder == $item} selected="selected"{/if}>{$item}</option>
            {/foreach}
        </select>
    </li>
    {/if}
</ul>

<div class="tab-content">{* Tab panes *}

{* -------------------- START #list tab-pane -------------------- *}
      <div role="tabpanel" class="tab-pane" id="list">
        {* the content will be loaded via AJAX *}
        <div class="fa fa-fw fa-3x text-center" style="width:100%"></div><span class="sr-only">Loading...</span>
        <div class="alert alert-danger" style="display:none;">
            {translate('Unable to load the tab!')}
        </div>
        {include file="backend_media_listtab.tpl"}
      </div>{* END #config tab-pane *}

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
<span class="bsFilterSelect pull-right" style="display:none">
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
{$file = cat('modules/lib_jquery/plugins/jquery.datatables/i18n/',lower($LANGUAGE),'.json')}
{* set javascript var for later use *}
<script type="text/javascript">
//<![CDATA[
    var CAT_ASSET_URL = "{cat_asset_url($file,'js')}";
//]]>
</script>


<pre style="display:none">
mime_type = 'image/jpeg'
filesize = 1563209
filepath = 'P:/BlackCat2/cat_engine/media'
filename = 'Firebird (002).jpg'
filenamepath = 'P:/BlackCat2/cat_engine/media/Firebird (002).jpg'
encoding = 'UTF-8'
error = 'n/a'
warning = 'n/a'
hfilesize = '1.49 MB'
moddate = '22-04-2016 12:39'
image = true
preview = '/media/Firebird (002).jpg'
exif (array):
    ExposureTime = 0.030303030303030304
    ISOSpeedRatings = 320
    ShutterSpeedValue = 5.0599999999999996
    FocalLength = 4.1500000000000004
    ExifImageWidth = 3264
    ExifImageLength = 2448
    DateTimeOriginal = '2016:04:22 11:45:04'
    Make = 'Apple'
    Model = 'iPhone 6'
    Orientation = 1
    XResolution = 72
    YResolution = 72
    FileDateTime = 1461328763
</pre>